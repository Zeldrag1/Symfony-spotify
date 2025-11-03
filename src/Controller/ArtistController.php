<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Service\AuthSpotifyService;
use App\Service\SpotifyRequestService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/artist')]
class ArtistController extends AbstractController
{
    private string $token;

    public function __construct(
        private readonly AuthSpotifyService    $authSpotifyService,
        private readonly SpotifyRequestService $spotifyRequestService
    )
    {
        $this->token = $this->authSpotifyService->auth();
    }

    #[Route('/like', name: 'app_artist_like', methods: ['POST'])]
    public function likeArtist(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['id'])) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $user = $this->getUser();
        $artist = $em->getRepository(Artist::class)->findOneBy(['spotifyId' => $data['id']]);

        if (!$artist) {
            $artist = (new Artist())
                ->setSpotifyId($data['id'])
                ->setName($data['name']);
            $em->persist($artist);
        }

        if ($user->getFavoriteArtists()->contains($artist)) {
            $user->removeFavoriteArtist($artist);
            $em->flush();
            return new JsonResponse(['success' => true, 'message' => 'Retiré des favoris']);
        }

        $user->addFavoriteArtist($artist);
        $em->flush();
        return new JsonResponse(['success' => true, 'message' => 'Ajouté aux favoris']);
    }

    #[Route('/favorites', name: 'app_artist_favorites')]
    public function favorites(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $artists = $user->getFavoriteArtists();

        return $this->render('artist/favorites.html.twig', [
            'artists' => $artists,
        ]);
    }

    #[Route('/{search?}', name: 'app_artist_index')]
    public function index(?string $search): Response
    {
        $artists = $this->spotifyRequestService->searchArtists($search ?: "kazzey", $this->token);

        $userFavorites = [];
        if ($this->getUser()) {
            $userFavorites = $this->getUser()->getFavoriteArtists()
                ->map(fn($artist) => $artist->getSpotifyId())
                ->toArray();
        }

        return $this->render('artist/index.html.twig', [
            'artists' => $artists,
            'search' => $search,
            'userFavorites' => $userFavorites,
        ]);
    }


}

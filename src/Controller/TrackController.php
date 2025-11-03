<?php

namespace App\Controller;

use App\Service\AuthSpotifyService;
use App\Service\SpotifyRequestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Track;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/track')]
class TrackController extends AbstractController
{
    private string $token;

    public function __construct(
        private readonly AuthSpotifyService    $authSpotifyService,
        private readonly SpotifyRequestService $spotifyRequestService
    )
    {
        $this->token = $this->authSpotifyService->auth();
    }

    #[Route('/like', name: 'app_track_like', methods: ['POST'])]
    public function likeTrack(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['id'])) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $user = $this->getUser();

        $track = $em->getRepository(Track::class)->findOneBy(['spotifyId' => $data['id']]);

        if (!$track) {
            $track = new Track();
            $track->setSpotifyId($data['id']);
            $track->setName($data['name']);
            $track->setSpotifyUrl('#');
            $em->persist($track);
        }

        if ($user->getTracks()->contains($track)) {
            $user->removeTrack($track);
            $em->flush();
            return new JsonResponse(['success' => true, 'message' => 'Retiré des favoris']);
        }

        $user->addTrack($track);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Ajouté aux favoris']);
    }


    #[Route('/favorites', name: 'app_track_favorites')]
    public function favorites(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $tracks = $user->getTracks();

        return $this->render('track/favorites.html.twig', [
            'tracks' => $tracks,
        ]);
    }

    #[Route('/{search?}', name: 'app_track_index')]
    public function index(?string $search): Response
    {
        $tracks = $this->spotifyRequestService->searchTracks($search ?: "kazzey", $this->token);

        $userFavorites = [];
        if ($this->getUser()) {
            $userFavorites = $this->getUser()->getTracks()
                ->map(fn($track) => $track->getSpotifyId())
                ->toArray();
        }

        return $this->render('track/index.html.twig', [
            'tracks' => $tracks,
            'search' => $search,
            'userFavorites' => $userFavorites,
        ]);
    }

    #[Route('/show/{id}', name: 'app_track_show')]
    public function show(string $id): Response
    {
        return $this->render('track/show.html.twig', [
            'track' => $this->spotifyRequestService->getTrack($id, $this->token),
        ]);
    }


}

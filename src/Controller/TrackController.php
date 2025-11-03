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
        dump($request->request->all());
        $data = json_decode($request->getContent(), true);
        dump($data);
        if (!$data || !isset($data['id'])) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $track = new Track();
        $track->setSpotifyUrl('#');
        $track->setSpotifyId($data['id']);
        $track->setName($data['name']);

        $em->persist($track);
        $em->flush();
        return new JsonResponse(['success' => true, 'message' => 'Track enregistrée']);
    }

    #[Route('/favorites', name: 'app_track_favorites')]
    public function favorites(EntityManagerInterface $em): Response
    {
        $tracks = $em->getRepository(Track::class)->findAll();

        return $this->render('track/favorites.html.twig', [
            'tracks' => $tracks,
        ]);
    }

    #[Route('/{search?}', name: 'app_track_index')]
    public function index(?string $search): Response
    {
        dump($search);
        return $this->render('track/index.html.twig', [
            'tracks' => $this->spotifyRequestService->searchTracks($search ?: "kazzey", $this->token),
            'search' => $search,
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

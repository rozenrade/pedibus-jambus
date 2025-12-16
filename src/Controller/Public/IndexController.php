<?php

namespace App\Controller\Public;

use App\Repository\AlbumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(AlbumRepository $albumRepository): Response
    {
        // Récupérer les 3 albums publics les plus récents (par date d'événement)
        $albums = $albumRepository->findRecentAlbums(3);
        
        return $this->render('public/home/index.html.twig', [
            'albums' => $albums,
        ]);
    }

    #[Route('/outlings', name:'app_outlings')]
    public function outlings(): Response
    {
        return $this->render('public/outlings/index.html.twig', []);
    }

    #[Route('/about-us', name:'app_about_us')]
    public function aboutUs(): Response
    {
        return $this->render('public/about-us/index.html.twig', []);
    }
}
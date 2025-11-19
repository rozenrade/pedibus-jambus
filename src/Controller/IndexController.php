<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', name:'app_home')]
    public function home(): Response
    {
        return $this->render('home/index.html.twig', []);
    }

    #[Route('/outlings', name:'app_outlings')]
    public function outlings(): Response
    {
        return $this->render('outlings/index.html.twig', []);
    }

    #[Route('/auth', name:'app_auth')]
    public function auth(): Response
    {
        return $this->render('auth/index.html.twig', []);
    }
}
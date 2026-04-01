<?php

namespace App\Controller;

use App\Entity\Album;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
  #[Route('/', name: 'app_home')]
  public function home(EntityManager $em): Response
  {
    $albums = $em->getRepository(Album::class)->findBy([], ['createdAt' => 'DESC']);
    return $this->render('home/index.html.twig', ['albums' => $albums]);
  }

  #[Route('/sorties', name: 'app_outlings')]
  public function outlings(): Response
  {
    return $this->render('outlings/index.html.twig', []);
  }

  #[Route('/a-propos', name: 'app_about_us')]
  public function aboutUs(): Response
  {
    return $this->render('about-us/index.html.twig', []);
  }

  #[Route('/album/{id}', name: 'album_show')]
  public function showAlbum(Album $album): Response
  {
    return $this->render('album/show.html.twig', [
      'album' => $album,
    ]);
  }
}

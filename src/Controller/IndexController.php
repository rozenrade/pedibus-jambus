<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Member;
use App\Repository\MemberRepository;
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
    return $this->render('public/home/index.html.twig', ['albums' => $albums]);
  }

  #[Route('/sorties', name: 'app_outlings')]
  public function outlings(): Response
  {
    return $this->render('public/outlings/index.html.twig', []);
  }

  #[Route('/a-propos', name: 'app_about_us')]
  public function aboutUs(MemberRepository $memberRepository): Response
  {
    return $this->render('public/about-us/index.html.twig', [
      'bureauPresident' => $memberRepository->findByCategory(Member::CATEGORY_BUREAU_PRESIDENT),
      'bureauMembers' => $memberRepository->findByCategory(Member::CATEGORY_BUREAU_MEMBER),
      'animateurs' => $memberRepository->findByCategory(Member::CATEGORY_ANIMATEUR),
      'webmestres' => $memberRepository->findByCategory(Member::CATEGORY_WEBMESTRE),
      'presidentHonneur' => $memberRepository->findByCategory(Member::CATEGORY_PRESIDENT_HONNEUR),
    ]);
  }

  #[Route('/album/{id}', name: 'album_show')]
  public function showAlbum(Album $album): Response
  {
    return $this->render('public/album/show.html.twig', [
      'album' => $album,
    ]);
  }
}

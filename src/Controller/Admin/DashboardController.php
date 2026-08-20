<?php
// src/Controller/Admin/DashboardController.php

namespace App\Controller\Admin;

use App\Repository\AlbumRepository;
use App\Repository\HikingProgramRepository;
use App\Repository\MemberRepository;
use App\Repository\PhotoRepository;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/panel', name: 'admin_dashboard')]
    public function index(
        AlbumRepository $albumRepository,
        PhotoRepository $photoRepository,
        UserRepository $userRepository,
        HikingProgramRepository $programRepository,
        MemberRepository $memberRepository,
        RecipeRepository $recipeRepository,
        CacheInterface $cache
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }

        $data = $cache->get('admin_dashboard', function (ItemInterface $item) use (
            $albumRepository,
            $photoRepository,
            $programRepository,
            $memberRepository,
            $recipeRepository
        ) {
            $item->expiresAfter(300);

            $since = new \DateTimeImmutable('-1 month');

            return [
                // Albums
                'totalAlbums' => $albumRepository->count([]),
                'publicAlbums' => $albumRepository->countPublic(),
                'privateAlbums' => $albumRepository->countPrivate(),
                'recentAlbumsCount' => $albumRepository->countRecentSince($since),
                'recentAlbums' => $albumRepository->findRecentWithPhotoCount(5),
                'topAlbums' => $albumRepository->findTopByPhotoCount(5),

                // Photos
                'totalPhotos' => $photoRepository->count([]),
                'recentPhotos' => $photoRepository->findRecentPhotos(5),
                'recentPhotosCount' => $photoRepository->countRecentSince($since),

                // Programmes
                'totalPrograms' => $programRepository->count([]),
                'recentProgramsCount' => $programRepository->countRecent(30),
                'programsByYear' => $programRepository->countByYear(),
                'recentPrograms' => $programRepository->findBy([], ['updateAt' => 'DESC'], 5),

                // Membres
                'totalMembers' => $memberRepository->count([]),
                'recentMembers' => $memberRepository->findRecentMembers(5),

                // Recettes
                'totalRecipes' => $recipeRepository->count([]),
                'recentRecipesCount' => $recipeRepository->countRecent(30),
                'recentRecipes' => $recipeRepository->findRecent(5),
                'recipesByCategory' => $recipeRepository->countByCategory(),
            ];
        });

        return $this->render('admin/dashboard/index.html.twig', $data);
    }

    #[Route('/stats-widget', name: 'admin_stats_widget')]
    public function statsWidget(AlbumRepository $albumRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('admin/dashboard/_stats_widget.html.twig', [
            'stats' => [
                'total' => $albumRepository->count([]),
                'publicCount' => $albumRepository->countPublic(),
                'privateCount' => $albumRepository->countPrivate(),
            ],
        ]);
    }
}
<?php
// src/Controller/Admin/DashboardController.php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\Photo;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\HikingProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function index(
        EntityManagerInterface $em,
        HikingProgramRepository $programRepository
    ): Response {
        // Statistiques générales
        $totalAlbums = $em->getRepository(Album::class)->count([]);
        $totalPhotos = $em->getRepository(Photo::class)->count([]);
        $totalUsers = $em->getRepository(User::class)->count([]);

        // Albums récents (5 derniers)
        $recentAlbums = $em->getRepository(Album::class)->findBy(
            [],
            ['createdAt' => 'DESC'],
            5
        );

        // Photos récentes (5 dernières)
        $recentPhotos = $em->getRepository(Photo::class)->findBy(
            [],
            ['updatedAt' => 'DESC'],
            5
        );

        // Statistiques par visibilité
        $publicAlbums = $em->getRepository(Album::class)->count(['isPublic' => true]);
        $privateAlbums = $em->getRepository(Album::class)->count(['isPublic' => false]);

        // Albums avec le plus de photos
        $qb = $em->createQueryBuilder();
        $topAlbums = $qb->select('a as album', 'COUNT(p.id) as photoCount')
            ->from(Album::class, 'a')
            ->leftJoin('a.photos', 'p')
            ->groupBy('a.id')
            ->orderBy('photoCount', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Statistiques temporelles
        $lastMonth = new \DateTime('-1 month');
        $recentAlbumsCount = $em->getRepository(Album::class)->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.createdAt >= :lastMonth')
            ->setParameter('lastMonth', $lastMonth)
            ->getQuery()
            ->getSingleScalarResult();

        $recentPhotosCount = $em->getRepository(Photo::class)->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.updatedAt >= :lastMonth')
            ->setParameter('lastMonth', $lastMonth)
            ->getQuery()
            ->getSingleScalarResult();



        $totalPrograms = $programRepository->count([]);
        $recentProgramsCount = $programRepository->countRecent(30); // 30 derniers jours
        $programsByYear = $programRepository->countByYear();

        // Récupérez les derniers programmes
        $recentPrograms = $programRepository->findBy(
            [],
            ['updateAt' => 'DESC'],
            5
        );

        return $this->render('admin/dashboard/index.html.twig', [
            'totalAlbums' => $totalAlbums,
            'totalPhotos' => $totalPhotos,
            'totalUsers' => $totalUsers,
            'recentAlbums' => $recentAlbums,
            'recentPhotos' => $recentPhotos,
            'publicAlbums' => $publicAlbums,
            'privateAlbums' => $privateAlbums,
            'topAlbums' => $topAlbums,
            'recentAlbumsCount' => $recentAlbumsCount,
            'recentPhotosCount' => $recentPhotosCount,
            'totalPrograms' => $totalPrograms,
            'recentProgramsCount' => $recentProgramsCount,
            'programsByYear' => $programsByYear,
            'recentPrograms' => $recentPrograms,
        ]);
    }

    #[Route('/quick-actions', name: 'admin_quick_actions')]
    public function quickActions(): Response
    {
        return $this->render('admin/dashboard/_quick_actions.html.twig');
    }

    #[Route('/stats-widget', name: 'admin_stats_widget')]
    public function statsWidget(EntityManagerInterface $em): Response
    {
        $stats = [
            'albums' => $em->getRepository(Album::class)->count([]),
            'photos' => $em->getRepository(Photo::class)->count([]),
            'users' => $em->getRepository(User::class)->count([]),
        ];

        return $this->render('admin/dashboard/_stats_widget.html.twig', [
            'stats' => $stats,
        ]);
    }
}

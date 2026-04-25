<?php
// src/Controller/Admin/DashboardController.php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\Comment;
use App\Entity\Photo;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\HikingProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/panel', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $em, HikingProgramRepository $programRepository, CacheInterface $cache): Response
    {

        $data = $cache->get('admin_dashboard', function (ItemInterface $item) use ($em, $programRepository) {

            $item->expiresAfter(300);

            $totalAlbums = $em->getRepository(Album::class)->count([]);
            $totalPhotos = $em->getRepository(Photo::class)->count([]);
            $totalUsers = $em->getRepository(User::class)->count([]);
            $totalComments = $em->getRepository(Comment::class)->count([]);

            $recentAlbums = $em->createQueryBuilder()
                ->select('a', 'COUNT(p.id) as photoCount')
                ->from(Album::class, 'a')
                ->leftJoin('a.photos', 'p')
                ->groupBy('a.id')
                ->orderBy('a.createdAt', 'DESC')
                ->setMaxResults(5)
                ->getQuery()
                ->getResult();

            $recentPhotos = $em->getRepository(Photo::class)->findBy(
                [],
                ['updatedAt' => 'DESC'],
                5
            );

            $publicAlbums = $em->getRepository(Album::class)->count(['isPublic' => true]);
            $privateAlbums = $em->getRepository(Album::class)->count(['isPublic' => false]);

            $qb = $em->createQueryBuilder();
            $topAlbums = $qb->select('a as album', 'COUNT(p.id) as photoCount')
                ->from(Album::class, 'a')
                ->leftJoin('a.photos', 'p')
                ->groupBy('a.id')
                ->orderBy('photoCount', 'DESC')
                ->setMaxResults(5)
                ->getQuery()
                ->getResult();

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
            $recentProgramsCount = $programRepository->countRecent(30);
            $programsByYear = $programRepository->countByYear();

            $recentPrograms = $programRepository->findBy(
                [],
                ['updateAt' => 'DESC'],
                5
            );

            return [
                'totalAlbums' => $totalAlbums,
                'totalPhotos' => $totalPhotos,
                'totalUsers' => $totalUsers,
                'totalComments' => $totalComments,
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

            ];
        });

        return $this->render('admin/dashboard/index.html.twig', $data);
    }

    #[Route('/actions-rapides', name: 'admin_quick_actions')]
    public function quickActions(): Response
    {
        return $this->render('admin/dashboard/_quick_actions.html.twig');
    }

    #[Route('/stats-widget', name: 'admin_stats_widget')]
    public function statsWidget(EntityManagerInterface $em): Response
    {
        $stats = $em->createQueryBuilder()
            ->select("
        COUNT(a.id) as total,
        SUM(CASE WHEN a.isPublic = true THEN 1 ELSE 0 END) as publicCount,
        SUM(CASE WHEN a.isPublic = false THEN 1 ELSE 0 END) as privateCount
    ")
            ->from(Album::class, 'a')
            ->getQuery()
            ->getSingleResult();

        return $this->render('admin/dashboard/_stats_widget.html.twig', [
            'stats' => $stats,
        ]);
    }
}

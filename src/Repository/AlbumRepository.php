<?php
// src/Repository/AlbumRepository.php
namespace App\Repository;

use App\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Album>
 */
class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    // Méthode pour récupérer les albums les plus récents
    public function findRecentAlbums(int $limit = 6): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // Vous pouvez ajouter d'autres méthodes personnalisées ici
    public function findPublicAlbums(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublic = :isPublic')
            ->setParameter('isPublic', true)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchPublicAlbums(string $query): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublic = :public')
            ->andWhere('a.title LIKE :query OR a.description LIKE :query')
            ->setParameter('public', true)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('a.eventDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentPublicAlbums(int $limit = 6): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublic = :public')
            ->setParameter('public', true)
            ->orderBy('a.eventDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countPhotos(int $albumId): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(p.id)')
            ->leftJoin('a.photos', 'p')
            ->where('a.id = :id')
            ->setParameter('id', $albumId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findFiltered(string $filter = 'all', string $sort = 'recent'): array
    {
        $qb = $this->createQueryBuilder('a');

        // Filtre
        if ($filter === 'public') {
            $qb->andWhere('a.isPublic = :isPublic')->setParameter('isPublic', true);
        } elseif ($filter === 'private') {
            $qb->andWhere('a.isPublic = :isPublic')->setParameter('isPublic', false);
        }

        // Tri
        switch ($sort) {
            case 'title':
                $qb->orderBy('a.title', 'ASC');
                break;
            case 'photos':
                $qb->leftJoin('a.photos', 'p')
                    ->groupBy('a.id')
                    ->orderBy('COUNT(p.id)', 'DESC');
                break;
            case 'recent':
            default:
                $qb->orderBy('a.createdAt', 'DESC');
                break;
        }

        return $qb->getQuery()->getResult();
    }
}

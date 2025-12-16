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

    public function findPublicAlbumsWithPhotos(int $limit = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.photos', 'p')
            ->addSelect('p')
            ->where('a.isPublic = :public')
            ->setParameter('public', true)
            ->orderBy('a.eventDate', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC'); // Tri des photos du plus récent au plus ancien

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}

<?php
// src/Repository/PhotoRepository.php

namespace App\Repository;

use App\Entity\Photo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Photo>
 */
class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    public function searchPublicPhotos(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.album', 'a')
            ->where('a.isPublic = :public')
            ->andWhere('p.title LIKE :query OR p.description LIKE :query')
            ->setParameter('public', true)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByTagInPublicAlbums(string $tag): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.album', 'a')
            ->where('a.isPublic = :public')
            ->andWhere('JSON_CONTAINS(p.tags, :tag) = 1')
            ->setParameter('public', true)
            ->setParameter('tag', '"' . $tag . '"')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentPublicPhotos(int $limit = 12): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.album', 'a')
            ->where('a.isPublic = :public')
            ->setParameter('public', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\HikingProgram;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HikingProgram>
 */
class HikingProgramRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HikingProgram::class);
    }

    // Filtres public
    
    public function countRecent(int $days = 30): int
    {
        $date = new \DateTime("-$days days");

        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.updateAt >= :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByYear(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.year, COUNT(p.id) as count')
            ->groupBy('p.year')
            ->orderBy('p.year', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPublicPrograms(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isPublic = :isPublic')
            ->setParameter('isPublic', true)
            ->orderBy('p.year', 'DESC')
            ->addOrderBy('p.quarter', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Filtres admin
    public function findFiltered(string $filter = 'all'): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($filter === 'public') {
            $qb->andWhere('p.isPublic = :isPublic')->setParameter('isPublic', true);
        } elseif ($filter === 'private') {
            $qb->andWhere('p.isPublic = :isPublic')->setParameter('isPublic', false);
        }

        return $qb
            ->orderBy('p.year', 'DESC')
            ->addOrderBy('p.quarter', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

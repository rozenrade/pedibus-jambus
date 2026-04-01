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
}

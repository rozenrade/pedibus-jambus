<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Member>
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.category = :category')
            ->setParameter('category', $category)
            ->orderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne tous les membres groupés par catégorie, pour l'admin.
     */
    public function findAllGrouped(): array
    {
        $members = $this->createQueryBuilder('m')
            ->orderBy('m.category', 'ASC')
            ->addOrderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult();

        $grouped = [];
        foreach ($members as $member) {
            $grouped[$member->getCategory()][] = $member;
        }

        return $grouped;
    }

    public function getNextPosition(string $category): int
    {
        $result = $this->createQueryBuilder('m')
            ->select('MAX(m.position) as maxPos')
            ->andWhere('m.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? $result + 1 : 0;
    }

    public function findRecentMembers(int $limit = 5): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}

<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * StaffRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class StaffRepository extends EntityRepository
{
    /**
     * Returns all the staffs in an array
     */
    public function findAllByKind(string $kind)
    {
        $kindCondition = 'all' !== $kind ? 's.kind = :kind' : '1 = 1';

        $qb = $this->createQueryBuilder('s')
            ->addSelect('p')
            ->leftJoin('s.person', 'p')
            ->where($kindCondition)
            ->andWhere('s.suppressed = 0')
            ->orderBy('p.lastname', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
        ;

        if ('all' !== $kind) {
            $qb->setParameter('kind', $kind);
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns all the staff corresponding to the searched term
     */
    public function findAllSearch(string $term)
    {
        return $this->createQueryBuilder('s')
            ->addSelect('p')
            ->leftJoin('s.person', 'p')
            ->where('LOWER(p.firstname) LIKE :term OR LOWER(p.lastname) LIKE :term')
            ->andWhere('s.suppressed = 0')
            ->andWhere('p.suppressed = 0')
            ->orderBy('p.lastname', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->getQuery()
        ;
    }

    /**
     * Returns the staff if not suppressed
     */
    public function findOneById(int $staffId)
    {
        return $this->createQueryBuilder('s')
            ->addSelect('p, z')
            ->leftJoin('s.person', 'p')
            ->leftJoin('s.driverZones', 'z')
            ->where('s.staffId = :staffId')
            ->andWhere('s.suppressed = 0')
            ->andWhere('p.suppressed = 0')
            ->orderBy('z.priority', 'ASC')
            ->setParameter('staffId', $staffId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

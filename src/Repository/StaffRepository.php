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
    public function findAllByKind($kind)
    {
        return $this->createQueryBuilder('s')
            ->addSelect('p, z')
            ->leftJoin('s.person', 'p')
            ->leftJoin('s.driverZones', 'z')
            ->where('s.kind = :kind')
            ->andWhere('s.suppressed = 0')
            ->orderBy('s.priority', 'ASC')
            ->addOrderBy('z.priority', 'ASC')
            ->setParameter('kind', $kind)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the staff if not suppressed
     */
    public function findOneById($staffId)
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

<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * DriverRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverRepository extends EntityRepository
{
    /**
     * Returns all the drivers in an array
     */
    public function findAll()
    {
        return $this->createQueryBuilder('d')
            ->addSelect('p')
            ->leftJoin('d.person', 'p')
            ->where('d.suppressed = 0')
            ->orderBy('p.lastname', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the driver if not suppressed
     */
    public function findOneById($driverId)
    {
        return $this->createQueryBuilder('d')
            ->addSelect('p')
            ->leftJoin('d.person', 'p')
            ->where('d.driverId = :driverId')
            ->andWhere('d.suppressed = 0')
            ->andWhere('p.suppressed = 0')
            ->setParameter('driverId', $driverId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

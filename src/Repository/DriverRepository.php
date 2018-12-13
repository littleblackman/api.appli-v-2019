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
            ->addSelect('p, z')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.driverZones', 'z')
            ->where('d.suppressed = 0')
            ->orderBy('d.priority', 'ASC')
            ->addOrderBy('z.priority', 'ASC')
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
            ->addSelect('p, z')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.driverZones', 'z')
            ->where('d.driverId = :driverId')
            ->andWhere('d.suppressed = 0')
            ->andWhere('p.suppressed = 0')
            ->orderBy('z.priority', 'ASC')
            ->setParameter('driverId', $driverId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

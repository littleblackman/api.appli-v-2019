<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * DriverPresenceRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverPresenceRepository extends EntityRepository
{
    /**
     * Returns all the drivers available for a specific date
     */
    public function findDriversByPresenceDate($date)
    {
        return $this->createQueryBuilder('pr')
            ->addSelect('d, z')
            ->leftJoin('pr.driver', 'd')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.driverZones', 'z')
            ->where('pr.date = :date')
            ->orderBy('d.priority', 'ASC')
            ->addOrderBy('z.priority', 'ASC')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult()
        ;
    }
}

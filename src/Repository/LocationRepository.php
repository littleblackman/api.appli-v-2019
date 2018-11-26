<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * LocationRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class LocationRepository extends EntityRepository
{
    /**
     * Returns all the locations in an array
     */
    public function findAll()
    {
        return $this->createQueryBuilder('l')
            ->where('l.suppressed = 0')
            ->orderBy('l.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the location if not suppressed
     */
    public function findOneById($locationId)
    {
        return $this->createQueryBuilder('l')
            ->where('l.locationId = :locationId')
            ->andWhere('l.suppressed = 0')
            ->setParameter('locationId', $locationId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

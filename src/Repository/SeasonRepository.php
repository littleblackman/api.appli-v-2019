<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * SeasonRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class SeasonRepository extends EntityRepository
{
    /**
     * Returns all the seasons in an array
     */
    public function findAll()
    {
        return $this->createQueryBuilder('s')
            ->where('s.isActive = 1')
            ->andWhere('s.suppressed = 0')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the season if not suppressed and active
     */
    public function findOneById($seasonId)
    {
        return $this->createQueryBuilder('s')
            ->where('s.seasonId = :seasonId')
            ->andWhere('s.isActive = 1')
            ->andWhere('s.suppressed = 0')
            ->setParameter('seasonId', $seasonId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

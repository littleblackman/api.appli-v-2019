<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * SportRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class SportRepository extends EntityRepository
{
    /**
     * Returns the sport if not suppressed and active
     */
    public function findOneById($sportId)
    {
        return $this->createQueryBuilder('s')
            ->where('s.sportId = :sportId')
            ->andWhere('s.suppressed = 0')
            ->setParameter('sportId', $sportId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

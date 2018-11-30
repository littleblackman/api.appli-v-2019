<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FoodRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class FoodRepository extends EntityRepository
{
    /**
     * Returns all the food available is isActive
     */
    public function findAllByStatus($status)
    {
        return $this->createQueryBuilder('f')
            ->where('f.status = :status')
            ->andWhere('f.suppressed = 0')
            ->orderBy('f.name', 'ASC')
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the ride if not suppressed
     */
    public function findOneById($foodId)
    {
        return $this->createQueryBuilder('f')
            ->where('f.foodId = :foodId')
            ->andWhere('f.suppressed = 0')
            ->setParameter('foodId', $foodId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

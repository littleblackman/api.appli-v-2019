<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * MealRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class MealRepository extends EntityRepository
{
    /**
     * Returns all the rides by date
     */
    public function findAllByDate($date)
    {
        return $this->createQueryBuilder('m')
            ->addSelect('ml', 'f')
            ->leftJoin('m.foods', 'ml')
            ->leftJoin('ml.food', 'f')
            ->where('m.date LIKE :date')
            ->andWhere('m.suppressed = 0')
            ->setParameter('date', $date . '%')
            ->orderBy('m.mealId', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the ride if not suppressed
     */
    public function findOneById($mealId)
    {
        return $this->createQueryBuilder('m')
            ->addSelect('ml', 'f')
            ->leftJoin('m.foods', 'ml')
            ->leftJoin('ml.food', 'f')
            ->where('m.mealId LIKE :mealId')
            ->andWhere('m.suppressed = 0')
            ->setParameter('mealId', $mealId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

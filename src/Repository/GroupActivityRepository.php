<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * GroupActivityRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class GroupActivityRepository extends EntityRepository
{
    /**
     * Returns all the groupActivities by date
     */
    public function findAllByDate($date)
    {
        return $this->createQueryBuilder('ga')
            ->where('ga.date LIKE :date')
            ->andWhere('ga.suppressed = 0')
            ->setParameter('date', $date . '%')
            ->orderBy('ga.date', 'ASC')
            ->addOrderBy('ga.start', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the groupActivity if not suppressed
     */
    public function findOneById($groupActivityId)
    {
        return $this->createQueryBuilder('ga')
            ->where('ga.groupActivityId = :groupActivityId')
            ->andWhere('ga.suppressed = 0')
            ->setParameter('groupActivityId', $groupActivityId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

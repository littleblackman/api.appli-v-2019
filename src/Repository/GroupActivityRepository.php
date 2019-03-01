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
            ->where('ga.date = :date')
            ->andWhere('ga.suppressed = 0')
            ->setParameter('date', $date)
            ->addOrderBy('ga.start', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the groupActivities by date and staff
     */
    public function findAllByDateByStaff($date, $staff)
    {
        return $this->createQueryBuilder('ga')
            ->addSelect('gas')
            ->leftJoin('ga.staff', 'gas')
            ->where('ga.date = :date')
            ->andWhere('gas.staff = :staff')
            ->andWhere('ga.suppressed = 0')
            ->setParameter('date', $date)
            ->setParameter('staff', $staff->getStaffId())
            ->orderBy('ga.date', 'ASC')
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

    /**
     * Returns all the groupActivity non-locked for a date
     */
    public function getAllNonLocked($date)
    {
        return $this->createQueryBuilder('ga')
            ->where('ga.date = :date')
            ->andWhere('ga.locked = :false OR ga.locked IS NULL')
            ->andWhere('ga.suppressed = 0')
            ->setParameter('date', $date)
            ->setParameter('false', false)
            ->getQuery()
            ->getResult()
        ;
    }
}

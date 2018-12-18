<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PickupRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PickupRepository extends EntityRepository
{
    /**
     * Returns all the pickups by date
     */
    public function findAllByDate($date)
    {
        return $this->createQueryBuilder('p')
            ->where('p.start LIKE :date')
            ->andWhere('p.suppressed = 0')
            ->setParameter('date', $date . '%')
            ->orderBy('p.postal', 'ASC')
            ->addOrderBy('p.start', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns all the pickups by status
     */
    public function findAllByStatus($date, $status)
    {
        $statusCondition = 'null' === $status ? 'p.status IS NULL' : 'p.status = :status';

        $qb = $this->createQueryBuilder('p')
            ->where($statusCondition)
            ->andWhere('p.start LIKE :date')
            ->andWhere('p.suppressed = 0')
            ->andWhere('p.ride IS NOT NULL')
            ->orderBy('p.ride', 'ASC')
            ->addOrderBy('p.sortOrder', 'ASC')
            ->setParameter('date', $date . '%')
        ;

        if ('null' !== $status) {
            $qb->setParameter('status', $status);
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns all the pickups that are not affected to a ride
     */
    public function findAllUnaffected($date)
    {
        return $this->createQueryBuilder('p')
            ->where('p.start LIKE :date')
            ->andWhere('p.ride IS NULL')
            ->andWhere('p.suppressed = 0')
            ->setParameter('date', $date . '%')
            ->orderBy('p.postal', 'ASC')
            ->addOrderBy('p.start', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the pickup if not suppressed
     */
    public function findOneById($pickupId)
    {
        return $this->createQueryBuilder('p')
            ->addSelect('c')
            ->leftJoin('p.child', 'c')
            ->where('p.pickupId = :pickupId')
            ->andWhere('p.suppressed = 0')
            ->setParameter('pickupId', $pickupId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PickupActivityRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PickupActivityRepository extends EntityRepository
{
    /**
     * Returns all the pickupActivities by date
     */
    public function findAllByDate($date, $kind)
    {
        return $this->createQueryBuilder('p')
            ->where('p.start LIKE :date')
            ->andWhere('p.kind = :kind')
            ->andWhere('p.suppressed = 0')
            ->setParameter('date', $date . '%')
            ->setParameter('kind', $kind)
            ->orderBy('p.postal', 'ASC')
            ->addOrderBy('p.start', 'ASC')
            ->addOrderBy('p.address', 'ASC')
            ->addOrderBy('p.pickupActivityId', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns all the pickupActivities by status
     */
    public function findAllByStatus($date, $status)
    {
        $statusCondition = 'null' === $status ? 'p.status IS NULL' : 'p.status = :status';

        $qb = $this->createQueryBuilder('p')
            ->where($statusCondition)
            ->andWhere('p.start LIKE :date')
            ->andWhere('p.suppressed = 0')
            ->orderBy('p.start', 'ASC')
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
     * Returns all the pickupActivity using the registrationId linked
     */
    public function findByRegistrationId($registrationId)
    {
        return $this->createQueryBuilder('p')
            ->where('p.registration = :registrationId')
            ->setParameter('registrationId', $registrationId)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the pickupActivity if not suppressed
     */
    public function findOneById($pickupActivityId)
    {
        return $this->createQueryBuilder('p')
            ->addSelect('c')
            ->leftJoin('p.child', 'c')
            ->where('p.pickupActivityId = :pickupActivityId')
            ->andWhere('p.suppressed = 0')
            ->setParameter('pickupActivityId', $pickupActivityId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

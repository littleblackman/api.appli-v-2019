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
     * Counts all the pickups that are not affected to a ride by postal
     */
    public function countAllUnaffected($date, $kind)
    {
        return $this->createQueryBuilder('p')
            ->select('p.postal, COUNT(p.postal) as countPostal')
            ->where('p.start LIKE :date')
            ->andWhere('p.kind = :kind')
            ->andWhere('p.ride IS NULL')
            ->andWhere('p.suppressed = 0')
            ->groupBy('p.postal')
            ->distinct()
            ->setParameter('date', $date . '%')
            ->setParameter('kind', $kind)
            ->orderBy('countPostal', 'DESC')
            ->addOrderBy('p.postal', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns all the pickups by date
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
            ->addOrderBy('p.pickupId', 'ASC')
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
    public function findAllUnaffected($date, $kind)
    {
        return $this->createQueryBuilder('p')
            ->where('p.start LIKE :date')
            ->andWhere('p.kind = :kind')
            ->andWhere('p.ride IS NULL')
            ->andWhere('p.suppressed = 0')
            ->setParameter('date', $date . '%')
            ->setParameter('kind', $kind)
            ->orderBy('p.start', 'ASC')
            ->addOrderBy('p.postal', 'ASC')
            ->addOrderBy('p.address', 'ASC')
            ->addOrderBy('p.pickupId', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns all the pickup using the registrationId linked
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
     * Returns the pickups to geocode
     */
    public function findGeocode()
    {
        return $this->createQueryBuilder('p')
            ->where('p.suppressed = 0')
            ->andWhere('p.address IS NOT NULL')
            ->andWhere('p.address != :empty')
            ->andWhere('p.latitude IS NULL OR p.longitude IS NULL')
            ->setParameter('empty', '')
            ->orderBy('p.pickupId', 'ASC')
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

    /**
     * Returns the pickup that correspond to date, kind and child
     */
    public function findOneByDateKindChild($date, $kind, $child)
    {
        return $this->createQueryBuilder('p')
            ->where('p.start LIKE :date')
            ->andWhere('p.kind = :kind')
            ->andWhere('p.child = :child')
            ->andWhere('p.ride IS NULL')
            ->andWhere('p.suppressed = 0')
            ->setParameter('date', $date . '%')
            ->setParameter('kind', $kind)
            ->setParameter('child', $child)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

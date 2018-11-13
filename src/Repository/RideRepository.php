<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RideRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class RideRepository extends EntityRepository
{
    /**
     * Returns all the rides by date
     */
    public function findAllByDate($date)
    {
        return $this->createQueryBuilder('r')
            ->addSelect('p', 'v')
            ->leftJoin('r.person', 'p')
            ->leftJoin('r.vehicle', 'v')
            ->where('r.date LIKE :date')
            ->andWhere('r.suppressed = 0')
            ->setParameter('date', $date . '%')
            ->orderBy('r.rideId', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns all the rides by status
     */
    public function findAllByStatus(string $status)
    {
        $operator = 'finished' === $status ? '<' : '>=';
        return $this->createQueryBuilder('r')
            ->addSelect('p', 'v')
            ->leftJoin('r.person', 'p')
            ->leftJoin('r.vehicle', 'v')
            ->where('r.date ' . $operator . ' :date')
            ->andWhere('r.suppressed = 0')
            ->orderBy('r.date', 'ASC')
            ->setParameter('date', date('Y-m-d', time()))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the ride by date and person
     */
    public function findOneByDateByPersonId($date, $person)
    {
        return $this->createQueryBuilder('r')
            ->addSelect('p', 'v')
            ->leftJoin('r.person', 'p')
            ->leftJoin('r.vehicle', 'v')
            ->where('r.date = :date')
            ->andWhere('r.person = :person')
            ->andWhere('r.suppressed = 0')
            ->setParameter('date', $date)
            ->setParameter('person', $person)
            ->orderBy('r.rideId', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Returns the ride if not suppressed
     */
    public function findOneById($rideId)
    {
        return $this->createQueryBuilder('r')
            ->addSelect('p', 'v', 'pi')
            ->leftJoin('r.person', 'p')
            ->leftJoin('r.vehicle', 'v')
            ->leftJoin('r.pickups', 'pi')
            ->where('r.rideId = :rideId')
            ->andWhere('r.suppressed = 0')
            ->andWhere('pi.suppressed = 0 OR pi.suppressed IS NULL')
            ->setParameter('rideId', $rideId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
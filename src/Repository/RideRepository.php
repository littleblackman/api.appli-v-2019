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
            ->getArrayResult()
        ;
    }

    /**
     * Returns all the rides in an array
     */
    public function findAllInArray(string $status)
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
            ->getArrayResult()
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
            ->getArrayResult()
        ;
    }

    /**
     * Returns the ride if not suppressed
     */
    public function findOneById($rideId)
    {
        return $this->createQueryBuilder('r')
            ->addSelect('p', 'v')
            ->leftJoin('r.person', 'p')
            ->leftJoin('r.vehicle', 'v')
            ->where('r.rideId = :rideId')
            ->andWhere('r.suppressed = 0')
            ->setParameter('rideId', $rideId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

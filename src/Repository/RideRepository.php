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
            ->addSelect('d', 'p', 'v', 'pi', 'z')
            ->leftJoin('r.driver', 'd')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.driverZones', 'z')
            ->leftJoin('r.vehicle', 'v')
            ->leftJoin('r.pickups', 'pi')
            ->where('r.date LIKE :date')
            ->andWhere('r.suppressed = 0')
            ->setParameter('date', $date . '%')
            ->orderBy('r.date', 'ASC')
            ->addOrderBy('r.start', 'ASC')
            ->addOrderBy('pi.sortOrder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns all the rides by date and kind
     */
    public function findAllByDateAndKind($date, $kind)
    {
        return $this->createQueryBuilder('r')
            ->addSelect('d', 'p', 'v', 'pi', 'z')
            ->leftJoin('r.driver', 'd')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.driverZones', 'z')
            ->leftJoin('r.vehicle', 'v')
            ->leftJoin('r.pickups', 'pi')
            ->where('r.date LIKE :date')
            ->andWhere('r.kind = :kind')
            ->andWhere('r.suppressed = 0')
            ->setParameter('date', $date . '%')
            ->setParameter('kind', $kind)
            ->orderBy('r.date', 'ASC')
            ->addOrderBy('r.start', 'ASC')
            ->addOrderBy('d.priority', 'ASC')
            ->addOrderBy('pi.sortOrder', 'ASC')
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
            ->addSelect('d', 'p', 'v', 'pi', 'z')
            ->leftJoin('r.driver', 'd')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.driverZones', 'z')
            ->leftJoin('r.vehicle', 'v')
            ->leftJoin('r.pickups', 'pi')
            ->where('r.date ' . $operator . ' :date')
            ->andWhere('r.suppressed = 0')
            ->orderBy('r.date', 'ASC')
            ->addOrderBy('r.start', 'ASC')
            ->addOrderBy('pi.sortOrder', 'ASC')
            ->setParameter('date', date('Y-m-d', time()))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the rides that are linked to another one for date
     */
    public function findAllLinked(string $date)
    {
        return $this->createQueryBuilder('r')
            ->where('r.date LIKE :date')
            ->andWhere('r.linkedRide IS NOT NULL')
            ->andWhere('r.suppressed = 0')
            ->setParameter('date', $date . '%')
            ->orderBy('r.start', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the rides by date and driver
     */
    public function findAllByDateByDriver($date, $driver)
    {
        return $this->createQueryBuilder('r')
            ->addSelect('d', 'p', 'v', 'pi', 'z')
            ->leftJoin('r.driver', 'd')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.driverZones', 'z')
            ->leftJoin('r.vehicle', 'v')
            ->leftJoin('r.pickups', 'pi')
            ->where('r.date = :date')
            ->andWhere('r.driver = :driver')
            ->andWhere('r.suppressed = 0')
            ->setParameter('date', $date)
            ->setParameter('driver', $driver->getDriverId())
            ->orderBy('r.date', 'ASC')
            ->addOrderBy('r.start', 'ASC')
            ->addOrderBy('pi.sortOrder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the ride by date and driver
     */
    public function findOneByDateByDriverId($date, $driverId)
    {
        return $this->createQueryBuilder('r')
            ->addSelect('d', 'p', 'v', 'pi', 'z')
            ->leftJoin('r.driver', 'd')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.driverZones', 'z')
            ->leftJoin('r.vehicle', 'v')
            ->leftJoin('r.pickups', 'pi')
            ->where('r.date = :date')
            ->andWhere('d.person = :person')
            ->andWhere('r.suppressed = 0')
            ->setParameter('date', $date)
            ->setParameter('driver', $driverId)
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
            ->addSelect('d', 'p', 'v', 'pi', 'z')
            ->leftJoin('r.driver', 'd')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.driverZones', 'z')
            ->leftJoin('r.vehicle', 'v')
            ->leftJoin('r.pickups', 'pi')
            ->where('r.rideId = :rideId')
            ->andWhere('r.suppressed = 0')
            ->andWhere('pi.suppressed = 0 OR pi.suppressed IS NULL')
            ->setParameter('rideId', $rideId)
            ->orderBy('pi.sortOrder', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

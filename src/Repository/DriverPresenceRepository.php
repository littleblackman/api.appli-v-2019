<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * DriverPresenceRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverPresenceRepository extends EntityRepository
{
    /**
     * Returns all the presence linked to the data provided
     */
    public function findByData($data)
    {
        $startCondition = isset($data['start']) ? 'pr.start = :start' : '1 = 1';
        $endCondition = isset($data['end']) ? 'pr.end = :end' : '1 = 1';

        $qb = $this->createQueryBuilder('pr')
            ->addSelect('d, z')
            ->leftJoin('pr.driver', 'd')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.driverZones', 'z')
            ->where('pr.suppressed = 0')
            ->andWhere('pr.driver = :driverId')
            ->andWhere('pr.date LIKE :date')
            ->andWhere($startCondition)
            ->andWhere($endCondition)
            ->orderBy('z.priority', 'ASC')
            ->setParameter('driverId', $data['driver'])
            ->setParameter('date', $data['date'] . '%')
        ;

        if (isset($data['start'])) {
            $qb->setParameter('start', $data['start']);
        }
        if (isset($data['end'])) {
            $qb->setParameter('end', $data['end']);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Returns all the presence by driver
     */
    public function findByDriver($driverId, $date)
    {
        $dateCriteria = null !== $date ? 'pr.date LIKE :date' : ' 1 = 1';

        $qb = $this->createQueryBuilder('pr')
            ->addSelect('d, z')
            ->leftJoin('pr.driver', 'd')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.driverZones', 'z')
            ->where('pr.driver = :driverId')
            ->andWhere($dateCriteria)
            ->orderBy('pr.date', 'ASC')
            ->addOrderBy('pr.start', 'ASC')
            ->addOrderBy('z.priority', 'ASC')
            ->setParameter('driverId', $driverId)
        ;

        if (null !== $date) {
            $qb->setParameter('date', $date . '%');
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns all the drivers available for a specific date
     */
    public function findDriversByPresenceDate($date)
    {
        return $this->createQueryBuilder('pr')
            ->addSelect('d, z')
            ->leftJoin('pr.driver', 'd')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.driverZones', 'z')
            ->where('pr.date = :date')
            ->orderBy('d.priority', 'ASC')
            ->addOrderBy('z.priority', 'ASC')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult()
        ;
    }
}

<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * StaffPresenceRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class StaffPresenceRepository extends EntityRepository
{
    /**
     * Returns all the presence linked to the data provided
     */
    public function findByData($data)
    {
        $startCondition = array_key_exists('start', $data) ? 'pr.start = :start' : '1 = 1';
        $endCondition = array_key_exists('end', $data) ? 'pr.end = :end' : '1 = 1';

        $qb = $this->createQueryBuilder('pr')
            ->addSelect('s, z')
            ->leftJoin('pr.staff', 's')
            ->leftJoin('s.person', 'p')
            ->leftJoin('s.driverZones', 'z')
            ->where('pr.suppressed = 0')
            ->andWhere('pr.staff = :staffId')
            ->andWhere('pr.date LIKE :date')
            ->andWhere($startCondition)
            ->andWhere($endCondition)
            ->orderBy('z.priority', 'ASC')
            ->setParameter('staffId', $data['staff'])
            ->setParameter('date', $data['date'] . '%')
        ;

        if (array_key_exists('start', $data)) {
            $qb->setParameter('start', $data['start']);
        }
        if (array_key_exists('end', $data)) {
            $qb->setParameter('end', $data['end']);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Returns all the presence by kind and date
     */
    public function findAllByKindAndDate($kind, $date)
    {
        $kindCriteria = 'all' !== $kind ? 's.kind = :kind' : '1 = 1';
        $dateCriteria = 'all' !== $date ? 'pr.date LIKE :date' : ' 1 = 1';

        $qb = $this->createQueryBuilder('pr')
            ->addSelect('s, z')
            ->leftJoin('pr.staff', 's')
            ->leftJoin('s.person', 'p')
            ->leftJoin('s.driverZones', 'z')
            ->where('pr.suppressed = 0')
            ->andWhere($kindCriteria)
            ->andWhere($dateCriteria)
            ->orderBy('pr.date', 'ASC')
            ->addOrderBy('pr.start', 'ASC')
            ->addOrderBy('z.priority', 'ASC')
        ;

        if ('all' !== $kind) {
            $qb->setParameter('kind', $kind);
        }
        if ('all' !== $date) {
            $qb->setParameter('date', $date . '%');
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns all the presence by staff
     */
    public function findByStaff($staffId, $date)
    {
        $dateCriteria = null !== $date ? 'pr.date LIKE :date' : ' 1 = 1';

        $qb = $this->createQueryBuilder('pr')
            ->addSelect('s, z')
            ->leftJoin('pr.staff', 's')
            ->leftJoin('s.person', 'p')
            ->leftJoin('s.driverZones', 'z')
            ->where('pr.staff = :staffId')
            ->andWhere('pr.suppressed = 0')
            ->andWhere($dateCriteria)
            ->orderBy('pr.date', 'ASC')
            ->addOrderBy('pr.start', 'ASC')
            ->addOrderBy('z.priority', 'ASC')
            ->setParameter('staffId', $staffId)
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
     * Returns all the staffs available for a specific date
     */
    public function findStaffsByPresenceDate($date)
    {
        return $this->createQueryBuilder('pr')
            ->addSelect('s, z')
            ->leftJoin('pr.staff', 's')
            ->leftJoin('s.person', 'p')
            ->leftJoin('s.driverZones', 'z')
            ->where('pr.suppressed = 0')
            ->andWhere('pr.date = :date')
            ->orderBy('s.priority', 'ASC')
            ->addOrderBy('z.priority', 'ASC')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult()
        ;
    }
}

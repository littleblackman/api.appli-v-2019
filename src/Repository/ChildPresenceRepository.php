<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ChildPresenceRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ChildPresenceRepository extends EntityRepository
{
    /**
     * Returns all the presence linked to the data provided
     */
    public function findByData($data)
    {
        $startCondition = array_key_exists('start', $data) ? 'pr.start = :start' : '1 = 1';
        $endCondition = array_key_exists('end', $data) ? 'pr.end = :end' : '1 = 1';

        $qb = $this->createQueryBuilder('pr')
            ->addSelect('c')
            ->leftJoin('pr.child', 'c')
            ->where('pr.suppressed = 0')
            ->andWhere('pr.child = :childId')
            ->andWhere('pr.date LIKE :date')
            ->andWhere($startCondition)
            ->andWhere($endCondition)
            ->setParameter('childId', $data['child'])
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
     * Returns all the presence by date
     */
    public function findAllByDate($date)
    {
        return $this->createQueryBuilder('pr')
            ->addSelect('c')
            ->leftJoin('pr.child', 'c')
            ->where('pr.suppressed = 0')
            ->andWhere('pr.date LIKE :date')
            ->orderBy('pr.date', 'ASC')
            ->addOrderBy('pr.start', 'ASC')
            ->setParameter('date', $date . '%')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns all the presence by child
     */
    public function findByChild($childId, $date)
    {
        $dateCriteria = null !== $date ? 'pr.date LIKE :date' : ' 1 = 1';

        $qb = $this->createQueryBuilder('pr')
            ->addSelect('c')
            ->leftJoin('pr.child', 'c')
            ->where('pr.child = :childId')
            ->andWhere('pr.suppressed = 0')
            ->andWhere($dateCriteria)
            ->orderBy('pr.date', 'ASC')
            ->addOrderBy('pr.start', 'ASC')
            ->setParameter('childId', $childId)
        ;

        if (null !== $date) {
            $qb->setParameter('date', $date . '%');
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }
}

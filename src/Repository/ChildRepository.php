<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ChildRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ChildRepository extends EntityRepository
{
    /**
     * Returns all the children in an array
     */
    public function findAll()
    {
        return $this->createQueryBuilder('c')
            ->where('c.suppressed = 0')
            ->orderBy('c.lastname', 'ASC')
            ->addOrderBy('c.firstname', 'ASC')
            ->getQuery()
        ;
    }

    /**
     * Returns all the children corresponding to the searched term
     */
    public function findAllSearch(string $term)
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.firstname) LIKE :term OR LOWER(c.lastname) LIKE :term')
            ->andWhere('c.suppressed = 0')
            ->orderBy('c.lastname', 'ASC')
            ->addOrderBy('c.firstname', 'ASC')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->getQuery()
        ;
    }

    /**
     * Returns the child if not suppressed
     */
    public function findOneById($childId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.childId = :childId')
            ->andWhere('c.suppressed = 0')
            ->setParameter('childId', $childId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function retrieveCurrentBirthdates($start, $n, $maxAge)
    {

        $start_sql = date("m-d", strtotime($start));
        $yearStart = date('Y', strtotime($start)) - $maxAge;

        $qb = $this->createQueryBuilder('c')
           ->where('c.suppressed = 0')
           ->andWhere("c.birthdate > '".$yearStart."-01-01'");

         $orModule = $qb->expr()->orx();
         for($i = 0; $i < $n; $i++) {
             $orModule->add($qb->expr()->like('c.birthdate', ':mydate'.$i));
         }

         $qb->andWhere($orModule);

         $current_date = $start;
         for($i = 0; $i < $n; $i++) {
             $current_date_sql = date("m-d", strtotime($current_date));
             $qb->setParameter('mydate'.$i, '%-'.$current_date_sql);
             $current_date = date('Y-m-d', strtotime($current_date.", +1 day"));
         }

         $qb->groupBy('c.childId');

         return $qb->orderBy('c.birthdate', 'ASC')
           ->getQuery()
           ->getResult();

    }
}

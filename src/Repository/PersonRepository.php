<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PersonRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PersonRepository extends EntityRepository
{
    /**
     * Returns all the persons in an array
     */
    public function findAllInArray()
    {
        return $this->createQueryBuilder('p')
            ->select('p.personId', 'p.firstname', 'p.lastname')
            ->where('p.suppressed = 0')
            ->orderBy('p.lastname', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * Returns all the persons corresponding to the searched term
     */
    public function findAllInSearch(string $term)
    {
        return $this->createQueryBuilder('p')
            ->select('p.personId', 'p.firstname', 'p.lastname')
            ->where('LOWER(p.firstname) LIKE :term OR LOWER(p.lastname) LIKE :term')
            ->andWhere('p.suppressed = 0')
            ->orderBy('p.lastname', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * Returns the person if not suppressed
     */
    public function findOneById($personId)
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'al', 'a', 'cl', 'c')
            ->leftJoin('p.addresses', 'al')
            ->leftJoin('al.address', 'a')
            ->leftJoin('p.children', 'cl')
            ->leftJoin('cl.child', 'c')
            ->where('p.personId = :personId')
            ->andWhere('p.suppressed = 0')
            ->andWhere('a.suppressed = 0 OR a.suppressed IS NULL')
            ->andWhere('c.suppressed = 0 OR c.suppressed IS NULL')
            ->setParameter('personId', $personId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

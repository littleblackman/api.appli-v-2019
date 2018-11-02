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
            ->where('p.personId = :personId')
            ->andWhere('p.suppressed = 0')
            ->setParameter('personId', $personId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

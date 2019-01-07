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
    public function findAll()
    {
        return $this->createQueryBuilder('p')
            ->where('p.suppressed = 0')
            ->orderBy('p.lastname', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns all the persons corresponding to the searched term
     */
    public function findAllSearch(string $term)
    {
        return $this->createQueryBuilder('p')
            ->where('LOWER(p.firstname) LIKE :term OR LOWER(p.lastname) LIKE :term')
            ->andWhere('p.suppressed = 0')
            ->orderBy('p.lastname', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the person using its user's identifier
     */
    public function findByUserIdentifier(string $identifier)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.userPersonLink', 'ul')
            ->leftJoin('ul.user', 'u')
            ->where('u.identifier = :identifier')
            ->andWhere('p.suppressed = 0')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Returns the person if not suppressed
     */
    public function findOneById($personId)
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'al', 'a', 'cl', 'c', 'ul', 'u')
            ->leftJoin('p.addresses', 'al')
            ->leftJoin('al.address', 'a')
            ->leftJoin('p.phones', 'pl')
            ->leftJoin('pl.phone', 'ph')
            ->leftJoin('p.children', 'cl')
            ->leftJoin('cl.child', 'c')
            ->leftJoin('p.userPersonLink', 'ul')
            ->leftJoin('ul.user', 'u')
            ->where('p.personId = :personId')
            ->andWhere('p.suppressed = 0')
            ->andWhere('a.suppressed = 0 OR a.suppressed IS NULL')
            ->andWhere('ph.suppressed = 0 OR ph.suppressed IS NULL')
            ->andWhere('c.suppressed = 0 OR c.suppressed IS NULL')
            ->setParameter('personId', $personId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

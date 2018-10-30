<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class PersonRepository extends EntityRepository
{
    /**
     * Returns all the persons in an array
     */
    public function findAllInArray()
    {
        return $this->createQueryBuilder('p')
            ->where('p.suppressed = 0')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * Returns the person if not suppressed
     */
    public function findOneById($id)
    {
        return $this->createQueryBuilder('p')
            ->where('p.personId = :id')
            ->andWhere('p.suppressed = 0')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Returns all the persons corresponding to the searched term
     */
    public function search(string $term, int $size)
    {
        return $this->createQueryBuilder('p')
            ->where('LOWER(p.firstname) LIKE :term OR LOWER(p.lastname) LIKE :term')
            ->andWhere('p.suppressed = 0')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->setMaxResults($size)
            ->getQuery()
            ->getResult()
        ;
    }
}

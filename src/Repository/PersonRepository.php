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
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * Returns all the persons corresponding to the searched term
     */
    public function search(string $term, int $size)
    {
        return $this->createQueryBuilder('p')
            ->where('LOWER(p.firstname) LIKE :term')
            ->orWhere('LOWER(p.lastname) LIKE :term')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->setMaxResults($size)
            ->getQuery()
            ->getResult()
        ;
    }
}

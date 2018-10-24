<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class ChildRepository extends EntityRepository
{
    /**
     * Returns all the children in an array
     */
    public function findAllInArray()
    {
        return $this->createQueryBuilder('c')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * Returns all the children corresponding to the searched term
     */
    public function search(string $term, int $size)
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.firstname) LIKE :term')
            ->orWhere('LOWER(c.lastname) LIKE :term')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->setMaxResults($size)
            ->getQuery()
            ->getResult()
        ;
    }
}

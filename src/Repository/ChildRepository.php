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
            ->where('c.suppressed = 0')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * Returns the child if not suppressed
     */
    public function findOneById($id)
    {
        return $this->createQueryBuilder('c')
            ->where('c.childId = :id')
            ->andWhere('c.suppressed = 0')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Returns all the children corresponding to the searched term
     */
    public function search(string $term, int $size)
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.firstname) LIKE :term OR LOWER(c.lastname) LIKE :term')
            ->andWhere('c.suppressed = 0')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->setMaxResults($size)
            ->getQuery()
            ->getResult()
        ;
    }
}

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
}

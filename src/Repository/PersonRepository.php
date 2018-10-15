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
}

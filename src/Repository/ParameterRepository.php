<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PersonRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ParameterRepository extends EntityRepository
{
    public function findAllGroupAge()
    {
        return $this->createQueryBuilder('p')
            ->where('p.name = :name')
            ->andWhere('p.isActive = :true')
            ->setParameter('name', 'GroupAge')
            ->setParameter('true', true)
            ->orderBy('p.value', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}

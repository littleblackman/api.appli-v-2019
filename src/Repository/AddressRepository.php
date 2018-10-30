<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class AddressRepository extends EntityRepository
{
    /**
     * Returns the address if not suppressed
     */
    public function findOneById($id)
    {
        return $this->createQueryBuilder('a')
            ->where('a.addressId = :id')
            ->andWhere('a.suppressed = 0')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

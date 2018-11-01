<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * AddressRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class AddressRepository extends EntityRepository
{
    /**
     * Returns the address if not suppressed
     */
    public function findOneById($addressId)
    {
        return $this->createQueryBuilder('a')
            ->where('a.addressId = :addressId')
            ->andWhere('a.suppressed = 0')
            ->setParameter('addressId', $addressId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

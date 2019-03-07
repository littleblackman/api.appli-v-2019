<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PersonRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ProductCancelledDateRepository extends EntityRepository
{
    /**
     * Returns all the ProductCancelledDate in an array
     */
    public function findAll()
    {
        return $this->createQueryBuilder('p')
            ->where('p.suppressed = 0')
            ->orderBy('p.date', 'ASC')
            ->getQuery()
        ;
    }

    /**
     * Returns all the ProductCancelledDate by date
     */
    public function findAllByDate($date)
    {
        return $this->createQueryBuilder('p')
            ->where('p.date LIKE :date')
            ->andWhere('p.suppressed = 0')
            ->setParameter('date', $date . '%')
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the ProductCancelledDate if not suppressed
     */
    public function findOneById($productCancelledDateId)
    {
        return $this->createQueryBuilder('p')
            ->where('p.productCancelledDateId = :productCancelledDateId')
            ->andWhere('p.suppressed = 0')
            ->setParameter('productCancelledDateId', $productCancelledDateId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

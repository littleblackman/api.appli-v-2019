<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ProductRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ProductRepository extends EntityRepository
{
    /**
     * Returns all the products in an array
     */
    public function findAll()
    {
        return $this->createQueryBuilder('p')
            ->where('p.suppressed = 0')
            ->orderBy('p.nameFr', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the product if not suppressed
     */
    public function findOneById($productId)
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'cl', 'c')
            ->leftJoin('p.components', 'cl')
            ->leftJoin('cl.component', 'c')
            ->where('p.productId = :productId')
            ->andWhere('p.suppressed = 0')
            ->andWhere('c.suppressed = 0 OR c.suppressed IS NULL')
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

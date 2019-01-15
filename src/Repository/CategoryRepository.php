<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CategoryRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class CategoryRepository extends EntityRepository
{
    /**
     * Returns the category if not suppressed and active
     */
    public function findOneById($categoryId)
    {
        return $this->createQueryBuilder('s')
            ->where('s.categoryId = :categoryId')
            ->andWhere('s.suppressed = 0')
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

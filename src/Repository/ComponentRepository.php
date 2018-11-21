<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ComponentRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ComponentRepository extends EntityRepository
{
    /**
     * Returns the component if not suppressed
     */
    public function findOneById($componentId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.componentId = :componentId')
            ->andWhere('c.suppressed = 0')
            ->setParameter('componentId', $componentId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

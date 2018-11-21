<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PhoneRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PhoneRepository extends EntityRepository
{
    /**
     * Returns the phone if not suppressed
     */
    public function findOneById($phoneId)
    {
        return $this->createQueryBuilder('p')
            ->where('p.phoneId = :phoneId')
            ->andWhere('p.suppressed = 0')
            ->setParameter('phoneId', $phoneId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

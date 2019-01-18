<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RegistrationRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class RegistrationRepository extends EntityRepository
{
    /**
     * Returns all the registrations in an array
     */
    public function findAll()
    {
        return $this->createQueryBuilder('r')
            ->where('r.suppressed = 0')
            ->orderBy('r.registration', 'DESC')
            ->getQuery()
        ;
    }

    /**
     * Returns the registration if not suppressed
     */
    public function findOneById($registrationId)
    {
        return $this->createQueryBuilder('r')
            ->where('r.registrationId = :registrationId')
            ->andWhere('r.suppressed = 0')
            ->setParameter('registrationId', $registrationId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

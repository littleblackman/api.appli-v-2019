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
     * Returns all the registrations related to status in an array
     */
    public function findAllByStatus($status)
    {
        $statusCondition = 'null' === $status ? 'r.status IS NULL' : 'r.status = :status';

        $qb = $this->createQueryBuilder('r')
            ->where($statusCondition)
            ->andWhere('r.suppressed = 0')
            ->orderBy('r.registration', 'DESC')
        ;

        if ('null' !== $status) {
            $qb->setParameter('status', $status);
        }

        return $qb
            ->getQuery()
        ;
    }

    /**
     * Returns all the registrations related to person and status in an array
     */
    public function findAllByPersonAndStatus($personId, $status)
    {
        $statusCondition = 'null' === $status ? 'r.status IS NULL' : 'r.status = :status';

        $qb = $this->createQueryBuilder('r')
            ->where($statusCondition)
            ->andWhere('r.person = :personId')
            ->andWhere('r.suppressed = 0')
            ->orderBy('r.registration', 'DESC')
            ->setParameter('personId', $personId)
        ;

        if ('null' !== $status) {
            $qb->setParameter('status', $status);
        }

        return $qb
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

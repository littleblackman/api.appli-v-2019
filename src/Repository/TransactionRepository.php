<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * TransactionRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class TransactionRepository extends EntityRepository
{
    /**
     * Returns all the transaction
     */
    public function findAll()
    {
        return $this->createQueryBuilder('t')
            ->where('t.suppressed = 0')
            ->orderBy('t.internalOrder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the transaction if not suppressed
     */
    public function findOneById($transactionId)
    {
        return $this->createQueryBuilder('t')
            ->where('t.transactionId = :transactionId')
            ->andWhere('t.suppressed = 0')
            ->setParameter('transactionId', $transactionId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

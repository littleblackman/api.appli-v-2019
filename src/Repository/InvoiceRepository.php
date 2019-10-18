<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * InvoiceRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class InvoiceRepository extends EntityRepository
{
    /**
     * Returns all the invoices in an array
     */
    public function findAll()
    {
        return $this->createQueryBuilder('i')
            ->where('i.suppressed = 0')
            ->orderBy('i.invoiceId', 'DESC')
            ->getQuery()
        ;
    }

    /**
     * Returns all the invoices corresponding to the searched term
     */
    public function findAllSearch(string $term)
    {
        return $this->createQueryBuilder('i')
            ->where('LOWER(i.nameFr) LIKE :term')
            ->andWhere('i.suppressed = 0')
            ->orderBy('i.nameFr', 'ASC')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->getQuery()
        ;
    }

    /**
     * Returns all the invoices with dates between those provided
     */
    public function findAllSearchByDates(string $dateStart, string $dateEnd)
    {
        $dateCondition = 'null' === $dateEnd ? 'i.date >= :dateStart' : 'i.date BETWEEN :dateStart AND :dateEnd';

        $qb = $this->createQueryBuilder('i')
            ->where($dateCondition)
            ->andWhere('i.suppressed = 0')
            ->orderBy('i.nameFr', 'ASC')
            ->setParameter('dateStart', $dateStart);
        ;

        if ('null' !== $dateEnd) {
            $qb->setParameter('dateEnd', $dateEnd);
        }

        return $qb
            ->getQuery()
        ;
    }

    /**
     * Returns the invoice if not suppressed
     */
    public function findOneById($invoiceId)
    {
        return $this->createQueryBuilder('i')
            ->where('i.invoiceId = :invoiceId')
            ->andWhere('i.suppressed = 0')
            ->setParameter('invoiceId', $invoiceId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

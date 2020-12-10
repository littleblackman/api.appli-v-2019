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

    public function findByStatus($dateStart, $dateEnd, $status = "paid", $mode = "all") {

        $qb = $this->createQueryBuilder('i')
        ->where('i.date BETWEEN :dateStart AND :dateEnd')
        ->andWhere('i.status = :status')
        ->andWhere('i.suppressed = 0');


        if($mode != "all") {
            $qb->andWhere('i.paymentMethod = :mode');
            $qb->setParameter('mode', strtoupper($mode));
        };

        return $qb->orderBy('i.date', 'DESC')
        ->setParameter('status', $status)
        ->setParameter('dateStart', $dateStart)
        ->setParameter('dateEnd', $dateEnd)
        ->getQuery()
        ->getResult()
        ;

    }

    public function findByPerson($person, $year) {
        return $this->createQueryBuilder('i')
        ->where('i.person = :person')
        ->andwhere('i.date LIKE :year')
        ->andWhere('i.status = :status')
        ->andWhere('i.suppressed = 0')
        ->orderBy('i.date', 'DESC')
        ->setParameter('person', $person)
        ->setParameter('year', $year.'%')
        ->setParameter('status', "paid")
        ->getQuery()
        ->getResult()
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

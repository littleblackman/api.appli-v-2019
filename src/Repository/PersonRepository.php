<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PersonRepository class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PersonRepository extends EntityRepository
{
    /**
     * Returns all the persons in an array
     */
    public function findAll()
    {
        return $this->createQueryBuilder('p')
            ->where('p.suppressed = 0')
            ->orderBy('p.lastname', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
            ->getQuery()
        ;
    }

    /**
     * Returns all the persons corresponding to the searched term
     */
    public function findAllSearch(string $term)
    {
        return $this->createQueryBuilder('p')
            ->where('LOWER(p.firstname) LIKE :term OR LOWER(p.lastname) LIKE :term')
            ->andWhere('p.suppressed = 0')
            ->orderBy('p.lastname', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->getQuery()
        ;
    }

    /**
     * Returns the person using its user's identifier
     */
    public function findByUserIdentifier(string $identifier)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.userPersonLink', 'ul')
            ->leftJoin('ul.user', 'u')
            ->where('u.identifier = :identifier')
            ->andWhere('p.suppressed = 0')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

        /**
     * Returns the person using its user's id
     */
    public function findByUserId(int $userId)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.userPersonLink', 'ul')
            ->leftJoin('ul.user', 'u')
            ->where('u.id = :userId')
            ->andWhere('p.suppressed = 0')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Returns the person using its user role
     */
    public function findByUserRole(string $role)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.userPersonLink', 'ul')
            ->leftJoin('ul.user', 'u')
            ->where('u.roles like :role')
            ->andWhere('p.suppressed = 0')
            ->setParameter('role', "%".$role."%")
            ->getQuery()
            ->getResult()
        ;
    }


    /**
     * Returns the person if not suppressed
     */
    public function findOneById($personId)
    {
        return $this->createQueryBuilder('p')
            ->where('p.personId = :personId')
            ->andWhere('p.suppressed = 0')
            ->setParameter('personId', $personId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }


    public function findDoublon($letter) {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT person_id, lastname, firstname FROM `person` WHERE lastname like "'.$letter.'%" ORDER BY lastname, firstname';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

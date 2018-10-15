<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Person;
use App\Service\PersonServiceInterface;

class PersonService implements PersonServiceInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllInArray()
    {
        return $this->em
            ->getRepository('App:Person')
            ->findAllInArray()
        ;
    }
}

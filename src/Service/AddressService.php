<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Address;
use App\Service\AddressServiceInterface;

class AddressService implements AddressServiceInterface
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
            ->getRepository('App:Address')
            ->findAllInArray()
        ;
    }
}

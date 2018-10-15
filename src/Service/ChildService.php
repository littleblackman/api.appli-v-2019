<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Child;
use App\Service\ChildServiceInterface;

class ChildService implements ChildServiceInterface
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
            ->getRepository('App:Child')
            ->findAllInArray()
        ;
    }
}

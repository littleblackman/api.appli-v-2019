<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
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
    public function delete(Child $child)
    {
        $child
            ->setSuppressed(true)
            ->setSuppressedAt(new \DateTime())
->setSuppressedBy(1)
        ;

        //Persists in DB
        $this->em->persist($child);
        $this->em->flush();

        return array(
            'status' => true,
            'message' => 'Enfant supprimé',
        );
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

    /**
     * {@inheritdoc}
     */
    public function hydrate(Child $child, ParameterBag $parameters)
    {
        foreach ($parameters as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($child, $method)) {
                $child->$method($value);
            } else {
                return array($key => $value);
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Child $child, ParameterBag $parameters)
    {
        $modify = $this->hydrate($child, $parameters);

        //Child updated
        if (!is_array($modify)) {
            $child
                ->setUpdatedAt(new \DateTime())
->setUpdatedBy(1)
            ;

            //Persists in DB
            $this->em->persist($child);
            $this->em->flush();

            $message = 'Enfant modifié';
        //Child NOT updated
        } else {
            $message = 'Erreur ! => ' . key($modify) . ' : ' . current($modify);
        }

        //Returns data
        return array(
            'status' => $modify,
            'message' => $message,
            'child' => $child->toArray(),
        );
    }
}

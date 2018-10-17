<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
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
    public function delete(Person $person)
    {
        $person
            ->setSuppressed(true)
            ->setSuppressedAt(new \DateTime())
->setSuppressedBy(1)
        ;

        //Persists in DB
        $this->em->persist($person);
        $this->em->flush();

        return array(
            'status' => true,
            'message' => 'Personne supprimée',
        );
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

    /**
     * {@inheritdoc}
     */
    public function hydrate(Person $person, ParameterBag $parameters)
    {
        foreach ($parameters as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($person, $method)) {
                $person->$method($value);
            } else {
                return array($key => $value);
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Person $person, ParameterBag $parameters)
    {
        $modify = $this->hydrate($person, $parameters);

        //Child updated
        if (!is_array($modify)) {
            $person
                ->setUpdatedAt(new \DateTime())
->setUpdatedBy(1)
            ;

            //Persists in DB
            $this->em->persist($person);
            $this->em->flush();

            $message = 'Personne modifiée';
        //Child NOT updated
        } else {
            $message = 'Erreur ! => ' . key($modify) . ' : ' . current($modify);
        }

        //Returns data
        return array(
            'status' => $modify,
            'message' => $message,
            'person' => $person->toArray(),
        );
    }
}

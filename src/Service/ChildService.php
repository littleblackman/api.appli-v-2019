<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\Child;
use App\Service\ChildServiceInterface;

class ChildService implements ChildServiceInterface
{
    private $em;
    private $user;

    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->em = $em;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function create(Child $child, ParameterBag $parameters)
    {
        if (null !== $parameters->get('firstname') && null !== $parameters->get('lastname')) {
            $create = $this->hydrate($child, $parameters);

            //Child created
            if (!is_array($create)) {
                $child
                    ->setCreatedAt(new \DateTime())
                    ->setCreatedBy($this->user->getId())
                    ->setSuppressed(false)
                ;

                //Persists in DB
                $this->em->persist($child);
                $this->em->flush();

                $message = 'Enfant ajouté';
            //Child NOT created
            } else {
                $message = 'Erreur ! => ' . key($create) . ' : ' . current($create);
            }

            //Returns data
            return array(
                'status' => $create,
                'message' => $message,
                'child' => $child->toArray(),
            );
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Child $child)
    {
        $child
            ->setSuppressed(true)
            ->setSuppressedAt(new \DateTime())
            ->setSuppressedBy($this->user->getId())
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
                ->setUpdatedBy($this->user->getId())
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

<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Address;
use App\Service\AddressServiceInterface;

class AddressService implements AddressServiceInterface
{
    private $em;
    private $security;
    private $user;

    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->em = $em;
        $this->security = $security;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function create(Address $address, ParameterBag $parameters)
    {
        if (null !== $parameters->get('name') && null !== $parameters->get('address')) {
            $create = $this->hydrate($address, $parameters);

            //Address created
            if (!is_array($create)) {
                $address
                    ->setCreatedAt(new \DateTime())
                    ->setCreatedBy($this->user->getId())
                    ->setSuppressed(false)
                ;

                //Persists in DB
                $this->em->persist($address);
                $this->em->flush();

                $message = 'Adresse ajoutée';
            //Address NOT created
            } else {
                $message = 'Erreur ! => ' . key($create) . ' : ' . current($create);
            }

            //Returns data
            return array(
                'status' => $create,
                'message' => $message,
                'address' => $this->filter($address->toArray()),
            );
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Address $address)
    {
        $address
            ->setSuppressed(true)
            ->setSuppressedAt(new \DateTime())
            ->setSuppressedBy($this->user->getId())
        ;

        //Persists in DB
        $this->em->persist($address);
        $this->em->flush();

        return array(
            'status' => true,
            'message' => 'Adresse supprimée',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $addressArray)
    {
        //Global data
        $globalData = array(
            '__initializer__',
            '__cloner__',
            '__isInitialized__',
        );

        //User's role linked data
        $specificData = array();
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $specificData = array_merge(
                $specificData,
                array(
                    'createdAt',
                    'createdBy',
                    'updatedAt',
                    'updatedBy',
                    'suppressed',
                    'suppressedAt',
                    'suppressedBy',
                )
            );
        }
        foreach (array_merge($globalData, $specificData) as $unsetData) {
            unset($addressArray[$unsetData]);
        }

        return $addressArray;
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

    /**
     * {@inheritdoc}
     */
    public function hydrate(Address $address, ParameterBag $parameters)
    {
        foreach ($parameters as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($address, $method)) {
                $address->$method($value);
            } else {
                return array($key => $value);
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Address $address, ParameterBag $parameters)
    {
        $modify = $this->hydrate($address, $parameters);

        //Child updated
        if (!is_array($modify)) {
            $address
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->user->getId())
            ;

            //Persists in DB
            $this->em->persist($address);
            $this->em->flush();

            $message = 'Adresse modifiée';
        //Child NOT updated
        } else {
            $message = 'Erreur ! => ' . key($modify) . ' : ' . current($modify);
        }

        //Returns data
        return array(
            'status' => $modify,
            'message' => $message,
            'address' => $this->filter($address->toArray()),
        );
    }
}

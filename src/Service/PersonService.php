<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Person;
use App\Service\AddressServiceInterface;
use App\Service\PersonServiceInterface;

class PersonService implements PersonServiceInterface
{
    private $addressService;
    private $em;
    private $security;
    private $user;

    public function __construct(
        AddressServiceInterface $addressService,
        EntityManagerInterface $em,
        Security $security,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->addressService = $addressService;
        $this->em = $em;
        $this->security = $security;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function create(Person $person, ParameterBag $parameters)
    {
        if (null !== $parameters->get('firstname') && null !== $parameters->get('lastname')) {
            $create = $this->hydrate($person, $parameters);

            //Person created
            if (!is_array($create)) {
                $person
                    ->setCreatedAt(new \DateTime())
                    ->setCreatedBy($this->user->getId())
                    ->setSuppressed(false)
                ;

                //Persists in DB
                $this->em->persist($person);
                $this->em->flush();

                $message = 'Personne ajoutée';
            //Person NOT created
            } else {
                $message = 'Erreur ! => ' . key($create) . ' : ' . current($create);
            }

            //Returns data
            return array(
                'status' => $create,
                'message' => $message,
                'person' => $this->filter($person->toArray()),
            );
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Person $person)
    {
        $person
            ->setSuppressed(true)
            ->setSuppressedAt(new \DateTime())
            ->setSuppressedBy($this->user->getId())
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
    public function filter(array $personArray)
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
        if ($this->security->isGranted('ROLE_TRAINEE') || $this->security->isGranted('ROLE_COACH')) {
            $specificData = array_merge(
                $specificData,
                array(
                    'addresses',
                )
            );

        }

        foreach (array_merge($globalData, $specificData) as $unsetData) {
            unset($personArray[$unsetData]);
        }

        //Filters addresses
        if (isset($personArray['addresses']) && is_array($personArray['addresses'])) {
            $addresses = array();
            foreach ($personArray['addresses'] as $key => $value) {
                $addresses[] = $this->addressService->filter($value);
            }
            $personArray['addresses'] = $addresses;
        }

        return $personArray;
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
                ->setUpdatedBy($this->user->getId())
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
            'person' => $this->filter($person->toArray()),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $term, int $size)
    {
        $persons = $this->em
            ->getRepository('App:Person')
            ->search($term, $size)
        ;

        $searchData = array();
        foreach ($persons as $person) {
            $searchData[] = $this->filter($person->toArray());
        }

        return $searchData;
    }
}

<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Person;
use App\Entity\UserPersonLink;
use App\Form\AppFormFactoryInterface;
use App\Service\AddressServiceInterface;
use App\Service\PersonServiceInterface;

/**
 * PersonService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PersonService implements PersonServiceInterface
{
    private $addressService;
    private $em;
    private $formFactory;
    private $security;
    private $user;

    public function __construct(
        AddressServiceInterface $addressService,
        EntityManagerInterface $em,
        AppFormFactoryInterface $formFactory,
        Security $security,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->addressService = $addressService;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->security = $security;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function create(Person $person, string $data)
    {
        $data = json_decode($data, true);
        $form = $this->formFactory->create('person-create', $person);
        $form->submit($data);

        $person
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($this->user->getId())
            ->setSuppressed(false)
        ;
        $this->em->persist($person);

        //Adds links from user to person
        $userPersonLink = new UserPersonLink();
        $userPersonLink
            ->setUser($this->user)
            ->setPerson($person)
        ;
        $this->em->persist($userPersonLink);

        //Persists in DB
        $this->em->flush();

        //Returns data
        return array(
            'status' => true,
            'message' => 'Personne ajoutée',
            'person' => $this->filter($person->toArray()),
        );
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
        $this->em->persist($person);

        //Removes links from user to person
        $userPersonLink = $this->em->getRepository('App:UserPersonLink')->findOneByPerson($person);
        if ($userPersonLink instanceof UserPersonLink) {
            $this->em->remove($userPersonLink);
        }

        //Removes links from person to child
        $childPersonLinks = $this->em->getRepository('App:ChildPersonLink')->findByPerson($person);
        foreach ($childPersonLinks as $childPersonLink) {
            if ($childPersonLink instanceof ChildPersonLink) {
                $this->em->remove($childPersonLink);
            }
        }

        //Removes links from person to address
        $personAddressLinks = $this->em->getRepository('App:PersonAddressLink')->findByPerson($person);
        foreach ($personAddressLinks as $personAddressLink) {
            if ($personAddressLink instanceof PersonAddressLink) {
                $this->em->remove($personAddressLink);
            }
        }

        //Persists in DB
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

        //Deletes unwanted data
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
    public function modify(Person $person, string $data)
    {
        $data = json_decode($data, true);
        $form = $this->formFactory->create('person-modify', $person);
        $form->submit($data);

        $person
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->user->getId())
        ;

        //Persists in DB
        $this->em->persist($person);
        $this->em->flush();

        //Returns data
        return array(
            'status' => true,
            'message' => 'Personne modifiée',
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

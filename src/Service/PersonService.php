<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
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
    private $mainService;

    public function __construct(
        AddressServiceInterface $addressService,
        EntityManagerInterface $em,
        MainServiceInterface $mainService
    )
    {
        $this->addressService = $addressService;
        $this->em = $em;
        $this->mainService = $mainService;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Person $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'person-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->create($object);
        $this->mainService->persist($object);

        //Adds links from user to person
        $userPersonLink = new UserPersonLink();
        $userPersonLink
            ->setUser($this->user)
            ->setPerson($object)
        ;
        $this->em->persist($userPersonLink);

        //Persists in DB
        $this->em->flush();
        $this->em->refresh($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Personne ajoutée',
            'person' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Person $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        //Removes links from user to person
        $userPersonLink = $this->em->getRepository('App:UserPersonLink')->findOneByPerson($object);
        if ($userPersonLink instanceof UserPersonLink) {
            $this->em->remove($userPersonLink);
        }

        //Removes links from person to child
        $childPersonLinks = $this->em->getRepository('App:ChildPersonLink')->findByPerson($object);
        foreach ($childPersonLinks as $childPersonLink) {
            if ($childPersonLink instanceof ChildPersonLink) {
                $this->em->remove($childPersonLink);
            }
        }

        //Removes links from person to address
        $objectAddressLinks = $this->em->getRepository('App:PersonAddressLink')->findByPerson($object);
        foreach ($objectAddressLinks as $objectAddressLink) {
            if ($objectAddressLink instanceof PersonAddressLink) {
                $this->em->remove($objectAddressLink);
            }
        }

        //Removes links from person to phone
        $objectPhoneLinks = $this->em->getRepository('App:PersonPhoneLink')->findByPerson($object);
        foreach ($objectPhoneLinks as $objectPhoneLink) {
            if ($objectPhoneLink instanceof PersonPhoneLink) {
                $this->em->remove($objectPhoneLink);
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
     * Returns the list of all persons in the array format
     * @return array
     */
    public function findAll()
    {
        return $this->em
            ->getRepository('App:Person')
            ->findAll()
        ;
    }

    /**
     * Searches the term in the Person collection
     * @return array
     */
    public function findAllSearch(string $term)
    {
        return $this->em
            ->getRepository('App:Person')
            ->findAllSearch($term)
        ;
    }

    /**
     * Returns the list of all the drivers
     * @return array
     */
    public function findDrivers()
    {
        return $this->em
            ->getRepository('App:Person')
            ->findDrivers()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Person $object)
    {
        if (null === $object->getFirstname() ||
            null === $object->getLastname()) {
            throw new UnprocessableEntityHttpException('Missing data for Person -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Person $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'person-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Personne modifiée',
            'person' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Person $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related addresses
        if (null !== $object->getAddresses()) {
            $addresses = array();
            foreach($object->getAddresses() as $addressLink) {
                $addresses[] = $this->mainService->toArray($addressLink->getAddress()->toArray());
            }
            $objectArray['addresses'] = $addresses;
        }

        //Gets related phones
        if (null !== $object->getPhones()) {
            $phones = array();
            foreach($object->getPhones() as $addressLink) {
                $phones[] = $this->mainService->toArray($addressLink->getPhone()->toArray());
            }
            $objectArray['phones'] = $phones;
        }

        //Gets related children
        if (null !== $object->getChildren()) {
            $children = array();
            foreach($object->getChildren() as $childLink) {
                $children[] = $this->mainService->toArray($childLink->getChild()->toArray());
            }
            $objectArray['children'] = $children;
        }

        return $objectArray;
    }
}

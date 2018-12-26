<?php

namespace App\Service;

use App\Entity\Person;
use App\Entity\UserPersonLink;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * PersonService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PersonService implements PersonServiceInterface
{
    private $addressService;

    private $driverService;

    private $em;

    private $mainService;

    public function __construct(
        AddressServiceInterface $addressService,
        DriverServiceInterface $driverService,
        EntityManagerInterface $em,
        MainServiceInterface $mainService
    )
    {
        $this->addressService = $addressService;
        $this->driverService = $driverService;
        $this->em = $em;
        $this->mainService = $mainService;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new Person();
        $data = $this->mainService->submit($object, 'person-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->create($object);
        $this->mainService->persist($object);

        //Adds links from user to person
        $user = isset($data['user']) ? $this->em->getRepository('App:User')->findOneById($data['user']) : null;
        $user = null !== $user ? $user : $this->mainService->getUser();//Covers the possibilty that no user is found
        $userPersonLink = new UserPersonLink();
        $userPersonLink
            ->setUser($user)
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
     * Returns the Person using its user's identifier
     * @return array
     */
    public function findByUserIdentifier($identifier)
    {
        return $this->em
            ->getRepository('App:Person')
            ->findByUserIdentifier($identifier)
        ;
    }

    /**
     * Finds one with its id
     * @return array
     */
    public function findOneById(int $personId)
    {
        return $this->em
            ->getRepository('App:Person')
            ->findOneById($personId)
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

        //Gets Driver if is one
        $driver = $this->em->getRepository('App:Driver')->findOneByPerson($object->getPersonId());
        $objectArray['driver'] = null !== $driver ? $this->driverService->toArray($driver) : null;

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

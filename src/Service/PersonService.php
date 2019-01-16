<?php

namespace App\Service;

use App\Entity\ChildPersonLink;
use App\Entity\Person;
use App\Entity\PersonAddressLink;
use App\Entity\PersonPersonLink;
use App\Entity\PersonPhoneLink;
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

    private $staffService;

    private $em;

    private $mainService;

    public function __construct(
        AddressServiceInterface $addressService,
        StaffServiceInterface $staffService,
        EntityManagerInterface $em,
        MainServiceInterface $mainService
    )
    {
        $this->addressService = $addressService;
        $this->staffService = $staffService;
        $this->em = $em;
        $this->mainService = $mainService;
    }

    /**
     * Adds relation between Person and Person
     */
    public function addRelation(int $relationId, string $relation, Person $object)
    {
        $related = $this->em->getRepository('App:Person')->findOneByPersonId($relationId);
        if ($related instanceof Person) {
            $personPersonLink = new PersonPersonLink();
            $personPersonLink
                ->setPerson($object)
                ->setRelated($related)
                ->setRelation($relation)
            ;
            $this->em->persist($personPersonLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new Person();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'person-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Adds links from user to person
        $user = array_key_exists('identifier', $data) ? $this->em->getRepository('App:User')->findOneByIdentifier($data['identifier']) : null;
        if (null !== $user) {
            $userPersonLink = new UserPersonLink();
            $userPersonLink
                ->setUser($user)
                ->setPerson($object)
            ;
            $this->em->persist($userPersonLink);
        }

        //Persists data
        $this->mainService->persist($object);

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

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

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

        //Adds relations
        if (array_key_exists('relations', $data)) {
            foreach ($data['relations'] as $relation) {
                $this->addRelation($relation['related'], $relation['relation'], $object);
            }
        }

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

        //Gets User's data
        if (null !== $object->getUserPersonLink() && null !== $object->getUserPersonLink()->getUser()) {
            $user = $object->getUserPersonLink()->getUser();
            $objectArray['email'] = $user->getEmail();
            $objectArray['identifier'] = $user->getIdentifier();
            unset($objectArray['userPersonLink']);
        }

        //Gets Driver if is one
        $driver = $this->em->getRepository('App:Staff')->findOneByPerson($object->getPersonId());
        $objectArray['driver'] = null !== $driver && 'driver' === $driver->getKind() ? $this->staffService->toArray($driver) : null;

        //Gets related addresses
        if (null !== $object->getAddresses()) {
            $addresses = array();
            foreach($object->getAddresses() as $addressLink) {
                if (!$addressLink->getAddress()->getSuppressed()) {
                    $addresses[] = $this->mainService->toArray($addressLink->getAddress()->toArray());
                }
            }
            $objectArray['addresses'] = $addresses;
        }

        //Gets related phones
        if (null !== $object->getPhones()) {
            $phones = array();
            foreach($object->getPhones() as $phoneLink) {
                if (!$phoneLink->getPhone()->getSuppressed()) {
                    $phones[] = $this->mainService->toArray($phoneLink->getPhone()->toArray());
                }
            }
            $objectArray['phones'] = $phones;
        }

        //Gets related children
        if (null !== $object->getChildren()) {
            $children = array();
            foreach($object->getChildren() as $childLink) {
                if (!$childLink->getChild()->getSuppressed()) {
                    $children[] = $this->mainService->toArray($childLink->getChild()->toArray());
                }
            }
            $objectArray['children'] = $children;
        }

        //Gets related persons
        if (null !== $object->getRelations()) {
            $relations = array();
            foreach($object->getRelations() as $relationLink) {
                if (!$relationLink->getRelated()->getSuppressed()) {
                    $relationArray = $this->toArray($relationLink->getRelated());
                    $relationArray['relation'] = $relationLink->getRelation();
                    $relations[] = $relationArray;
                }
            }
            $objectArray['relations'] = $relations;
        }

        return $objectArray;
    }
}

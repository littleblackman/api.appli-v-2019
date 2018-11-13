<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Entity\Address;
use App\Entity\Person;
use App\Entity\PersonAddressLink;
use App\Service\AddressServiceInterface;

/**
 * AddressService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class AddressService implements AddressServiceInterface
{
    private $em;
    private $mainService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
    }

    /**
     * {@inheritdoc}
     */
    public function addLink(int $personId, Address $object)
    {
        $person = $this->em->getRepository('App:Person')->findOneById($personId);
        if ($person instanceof Person) {
            $personAddressLink = new PersonAddressLink();
            $personAddressLink
                ->setPerson($person)
                ->setAddress($object)
            ;
            $this->em->persist($personAddressLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(Address $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'address-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->create($object);
        $this->mainService->persist($object);

        //Adds links from person/s to address
        $links = $data['links'];
        if (null !== $links && is_array($links) && !empty($links)) {
            $this->addLink((int) $links['personId'], $object);

            //Persists in DB
            $this->em->flush();
            $this->em->refresh($object);
        }

        //Returns data
        return array(
            'status' => true,
            'message' => 'Adresse ajoutée',
            'address' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Address $object, string $data)
    {
        $data = json_decode($data, true);

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        //Removes links from person/s to address
        $links = $data['links'];
        if (null !== $links && is_array($links) && !empty($links)) {
            $this->removeLink((int) $links['personId'], $object);

            //Persists in DB
            $this->em->flush();
        }

        return array(
            'status' => true,
            'message' => 'Adresse supprimée',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Address $object)
    {
        if (null === $object->getName() ||
            null === $object->getAddress() ||
            null === $object->getPostal() ||
            null === $object->getTown()) {
            throw new UnprocessableEntityHttpException('Missing data for Address -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Address $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'address-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Adresse modifiée',
            'address' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeLink(int $personId, Address $object)
    {
        $person = $this->em->getRepository('App:Person')->findOneById($personId);
        if ($person instanceof Person) {
            $personAddressLink = $this->em->getRepository('App:PersonAddressLink')->findOneBy(array('person' => $person, 'address' => $object));
            $this->em->remove($personAddressLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Address $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related persons
        if (null !== $object->getPersons()) {
            $persons = array();
            foreach($object->getPersons() as $personLink) {
                $persons[] = $this->mainService->toArray($personLink->getPerson()->toArray());
            }
            $objectArray['persons'] = $persons;
        }

        return $objectArray;
    }
}

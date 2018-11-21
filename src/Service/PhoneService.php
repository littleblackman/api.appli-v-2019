<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Entity\Phone;
use App\Entity\Person;
use App\Entity\PersonPhoneLink;
use App\Service\PhoneServiceInterface;

/**
 * PhoneService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PhoneService implements PhoneServiceInterface
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
    public function addLink(int $personId, Phone $object)
    {
        $person = $this->em->getRepository('App:Person')->findOneById($personId);
        if ($person instanceof Person) {
            $personPhoneLink = new PersonPhoneLink();
            $personPhoneLink
                ->setPerson($person)
                ->setPhone($object)
            ;
            $this->em->persist($personPhoneLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(Phone $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'phone-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->create($object);
        $this->mainService->persist($object);

        //Adds links from person/s to phone
        if (isset($data['links'])) {
            $links = $data['links'];

            if (null !== $links && is_array($links) && !empty($links)) {
                $this->addLink((int) $links['personId'], $object);

                //Persists in DB
                $this->em->flush();
                $this->em->refresh($object);
            }
        }

        //Returns data
        return array(
            'status' => true,
            'message' => 'Téléphone ajouté',
            'phone' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Phone $object, string $data)
    {
        $data = json_decode($data, true);

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        //Removes links from person/s to phone
        $links = $data['links'];
        if (null !== $links && is_array($links) && !empty($links)) {
            $this->removeLink((int) $links['personId'], $object);

            //Persists in DB
            $this->em->flush();
        }

        return array(
            'status' => true,
            'message' => 'Téléphone supprimé',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Phone $object)
    {
        if (null === $object->getName() ||
            null === $object->getPhone()) {
            throw new UnprocessableEntityHttpException('Missing data for Phone -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Phone $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'phone-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Téléphone modifié',
            'phone' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeLink(int $personId, Phone $object)
    {
        $person = $this->em->getRepository('App:Person')->findOneById($personId);
        if ($person instanceof Person) {
            $personPhoneLink = $this->em->getRepository('App:PersonPhoneLink')->findOneBy(array('person' => $person, 'phone' => $object));
            $this->em->remove($personPhoneLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Phone $object)
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

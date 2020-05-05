<?php

namespace App\Service;

use App\Entity\ChildPresence;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Entity\PickupActivity;
use App\Entity\Pickup;

/**
 * ChildPresenceService class.
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ChildPresenceService implements ChildPresenceServiceInterface
{
    private $em;

    private $childService;

    private $mainService;

    private $pickupActivityService;

    public function __construct(
        EntityManagerInterface $em,
        ChildServiceInterface $childService,
        MainServiceInterface $mainService,
        PickupActivityServiceInterface $pickupActivityService
    ) {
        $this->em = $em;
        $this->childService = $childService;
        $this->mainService = $mainService;
        $this->pickupActivityService = $pickupActivityService;
    }

    /**
     * Adds specific data that could not be added via generic method.
     */
    public function addSpecificData(ChildPresence $object, array $data)
    {
        //Should be done from ChildPresenceType but it returns null...
        if (array_key_exists('start', $data)) {
            $object->setStart(DateTime::createFromFormat('H:i:s', $data['start']));
        }
        if (array_key_exists('end', $data)) {
            $object->setEnd(DateTime::createFromFormat('H:i:s', $data['end']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        $data = json_decode($data, true);
        if (is_array($data) && !empty($data)) {
            foreach ($data as $childPresence) {
                $object = $this->em->getRepository('App:ChildPresence')->findByData($childPresence);
                //Creates object if not already existing
                if (null === $object) {
                    $object = new ChildPresence();
                    $this->mainService->create($object);

                    //Submits data
                    $this->mainService->submit($object, 'child-presence-create', $childPresence);
                    $this->addSpecificData($object, $childPresence);

                    //Checks if entity has been filled
                    $this->isEntityFilled($object);

                    //Persists data
                    $this->mainService->persist($object);
                }
            }

            //Returns data
            return array(
                'status' => true,
                'message' => 'ChildPresence ajoutées',
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> '.json_encode($data));
    }

    public function createFromRegistrationAndData($registration, $date, $location, $hours, $sports, $hasLunch, $hasTransport, $pickupsData)
    {
        // $presenceDate = new \DateTime($date);

        // create presence

        $object = new ChildPresence();
        $this->mainService->create($object);

        $object->setRegistration($registration);
        $object->setChild($registration->getChild());
        $object->setLocation($location);
        $object->setDate($date);
        $object->setStart($hours['start']);
        $object->setEnd($hours['end']);
        $object->setStatus(' ');

        $this->mainService->persist($object);

        // create pickup activity
        foreach ($sports as $sport) {
            $object = new PickupActivity();
            $this->mainService->create($object);

            $object->setDate($date);
            $object->setRegistration($registration);
            $object->setChild($registration->getChild());
            $object->setStart($hours['start']);
            $object->setEnd($hours['end']);
            $object->setSport($sport);
            $object->setLocation($location);

            //Persists data
            $this->mainService->persist($object);
        }

        // ahd lunch
        if($hasLunch == 1) {

            $lunch = $this->em->getRepository('App:Sport')->find(10);

            $object = new PickupActivity();
            $this->mainService->create($object);

            $object->setDate($date);
            $object->setRegistration($registration);
            $object->setChild($registration->getChild());
            $object->setStart($hours['start']);
            $object->setEnd($hours['end']);
            $object->setSport($lunch);
            $object->setLocation($location);

            //Persists data
            $this->mainService->persist($object);
        }


        // create pickup
        if ($hasTransport) {
            $child = $registration->getChild();
            $person = $child->getPersons()[0]->getPerson();

            $address = $person->getAddresses()[0]->getAddress();

            foreach ($pickupsData as $kind => $timePickup) {
                $start = new \DateTime($date->format('Y-m-d').' '.$timePickup->format('H:i:s'));

                //Submits data
                $object = new Pickup();
                $this->mainService->create($object);

                $object->setChild($registration->getChild());
                $object->setKind($kind);
                $object->setStart($start);
                $object->setRegistration($registration);
                $object->setPhone(null);
                $object->setPostal($address->getPostal());
                $object->setAddress($address->getAddress().', '.$address->getPostal().' '.$address->getTown());

                //Checks coordinates
                $this->checkCoordinates($object);
                $this->mainService->persist($object);
            }
        }
    }

    public function checkCoordinates($object, $force = false)
    {
        if ($force ||
            null === $object->getLatitude() ||
            null === $object->getLongitude() ||
            null === $object->getPostal() ||
            5 != strlen($object->getPostal())
        ) {
            $this->mainService->addCoordinates($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ChildPresence $object, $return = true)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        if ($return) {
            return array(
                'status' => true,
                'message' => 'ChildPresence supprimée',
            );
        }
    }

    /**
     * Deletes ChildPresence by array of ids.
     */
    public function deleteByArray(string $data)
    {
        $data = json_decode($data, true);
        if (is_array($data) && !empty($data)) {
            foreach ($data as $childPresence) {
                $childPresence = $this->em->getRepository('App:ChildPresence')->findByData($childPresence);
                if ($childPresence instanceof ChildPresence) {
                    $this->delete($childPresence, false);
                }
            }

            return array(
                'status' => true,
                'message' => 'ChildPresence supprimées',
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> '.json_encode($data));
    }

    /**
     * Deletes ChildPresence by registrationId.
     */
    public function deleteByRegistrationId(int $registrationId)
    {
        $childPresences = $this->em->getRepository('App:ChildPresence')->findByRegistrationId($registrationId);
        if (!empty($childPresences)) {
            foreach ($childPresences as $childPresence) {
                $this->delete($childPresence, false);
            }

            return array(
                'status' => true,
                'message' => 'ChildPresence supprimées',
            );
        }
    }

    /**
     * Returns the list of all children presence by date.
     *
     * @return array
     */
    public function findAllByDate($date)
    {
        return $this->em
            ->getRepository('App:ChildPresence')
            ->findAllByDate($date)
        ;
    }

    /**
     * Returns the list of presence by child.
     *
     * @return array
     */
    public function findByChild($childId, $date)
    {
        return $this->em
            ->getRepository('App:ChildPresence')
            ->findByChild($childId, $date)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(ChildPresence $object)
    {
        if (null === $object->getChild() ||
            null === $object->getDate() ||
            null === $object->getStart() ||
            null === $object->getLocation()) {
            throw new UnprocessableEntityHttpException('Missing data for ChildPresence -> '.json_encode($object->toArray()));
        }
    }

    public function updateStatus($child, $date, $status)
    {
        $presence = $this->em->getRepository('App:ChildPresence')->findOneBy(['child' => $child, 'date' => $date]);
        if ($status == null) {
            $status = '';
        }
        $presence->setStatus($status);
        $presence->setStatusChange(new DateTime());

        $this->em->persist($presence);
        $this->em->flush();

        if ($pickupActivitys = $this->em->getRepository('App:PickupActivity')->findBy(['child' => $child, 'date' => $date])) {
            foreach ($pickupActivitys as $pa) {
                $pa->setStatus($status);
                $pa->setStatusChange(new DateTime());
                $this->em->persist($pa);
                $this->em->flush();
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(ChildPresence $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related registration
        if (null !== $object->getRegistration() && !$object->getRegistration()->getSuppressed()) {
            $objectArray['registration'] = $this->mainService->toArray($object->getRegistration()->toArray());
        }

        //Gets related child
        if (null !== $object->getChild() && !$object->getChild()->getSuppressed()) {
            $objectArray['child'] = $this->mainService->toArray($object->getChild()->toArray());
        }

        //Gets related person
        if (null !== $object->getPerson() && !$object->getPerson()->getSuppressed()) {
            $objectArray['person'] = $this->mainService->toArray($object->getPerson()->toArray());
        }

        //Gets related location
        if (null !== $object->getLocation() && !$object->getLocation()->getSuppressed()) {
            $objectArray['location'] = $this->mainService->toArray($object->getLocation()->toArray());
        }

        return $objectArray;
    }
}

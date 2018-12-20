<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Service\MainServiceInterface;
use App\Service\DriverServiceInterface;
use App\Entity\Person;
use App\Entity\Ride;

/**
 * RideService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class RideService implements RideServiceInterface
{
    private $em;
    private $mainService;
    private $driverService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService,
        DriverServiceInterface $driverService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
        $this->driverService = $driverService;
    }

    /**
     * Adds time's data (should be done from RideType but it returns null...)
     */
    public function addTimeData(Ride $object, array $data)
    {
        if (isset($data['start'])) {
            $object->setStart(\DateTime::createFromFormat('H:i:s', $data['start']));
        }
        if (isset($data['arrival'])) {
            $object->setArrival(\DateTime::createFromFormat('H:i:s', $data['arrival']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(Ride $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'ride-create', $data);
        $this->addTimeData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->create($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Trajet ajouté',
            'ride' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createMultiple(string $data)
    {
        $data = is_array($data) ? $data : json_decode($data, true);

        foreach ($data as $rideData) {
            //Submits data
            $object = new Ride();
            $this->mainService->submit($object, 'ride-create', $rideData);
            $this->addTimeData($object, $rideData);

            //Checks if entity has been filled
            $this->isEntityFilled($object);

            //Persists data
            $this->mainService->create($object);
            $this->mainService->persist($object);
        }

        //Returns data
        return array(
            'status' => true,
            'message' => 'Trajets ajoutés',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Ride $object)
    {
        //Removes ride from pickups
        foreach ($object->getPickups() as $pickup) {
            $pickup
                ->setRide(null)
                ->setSortOrder(null)
            ;
            $this->mainService->modify($pickup);
            $this->em->persist($pickup);
        }

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'Trajet supprimé',
        );
    }

    /**
     * Returns the list of all rides by date
     * @return array
     */
    public function findAllByDate(string $date)
    {
        return $this->em
            ->getRepository('App:Ride')
            ->findAllByDate($date)
        ;
    }

    /**
     * Returns all the rides by status
     * @return array
     */
    public function findAllByStatus(string $status)
    {
        return $this->em
            ->getRepository('App:Ride')
            ->findAllByStatus($status)
        ;
    }

    /**
     * Returns the rides linked to date and person
     * @return array
     */
    public function findAllByDateByPersonId(string $date, Person $person)
    {
        return $this->em
            ->getRepository('App:Ride')
            ->findAllByDateByPersonId($date, $person)
        ;
    }

    /**
     * Returns the ride linked to date and driver
     * @return array
     */
    public function findAllByDateByDriverId(string $date, $driverId)
    {
        return $this->em
            ->getRepository('App:Ride')
            ->findAllByDateByDriverId($date, $driverId)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Ride $object)
    {
        if (null === $object->getDate() ||
            null === $object->getName()) {
            throw new UnprocessableEntityHttpException('Missing data for Ride -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Ride $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'ride-modify', $data);
        $this->addTimeData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Trajet modifié',
            'ride' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Ride $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related driver
        if (null !== $object->getDriver()) {
            $objectArray['driver'] = $this->driverService->toArray($object->getDriver());
        }

        //Gets related vehicle
        if (null !== $object->getVehicle()) {
            $objectArray['vehicle'] = $this->mainService->toArray($object->getVehicle()->toArray());
        }

        //Gets related pickups
        if (null !== $object->getPickups()) {
            $pickups = array();
            $i = 0;
            foreach($object->getPickups() as $pickup) {
                $pickups[$i] = $this->mainService->toArray($pickup->toArray());
                $pickups[$i]['child'] = $this->mainService->toArray($pickup->getChild()->toArray());
                $i++;
            }
            $objectArray['pickups'] = $pickups;
        }

        return $objectArray;
    }
}

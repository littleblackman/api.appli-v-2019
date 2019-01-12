<?php

namespace App\Service;

use App\Entity\Person;
use App\Entity\Ride;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * RideService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class RideService implements RideServiceInterface
{
    private $em;

    private $mainService;

    private $driverService;

    private $vehicleService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService,
        DriverServiceInterface $driverService,
        VehicleServiceInterface $vehicleService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
        $this->driverService = $driverService;
        $this->vehicleService = $vehicleService;
    }

    /**
     * Adds specific data that could not be added via generic method
     */
    public function addSpecificData(Ride $object, array $data)
    {
        //Should be done from RideType but it returns null...
        if (isset($data['start'])) {
            $object->setStart(DateTime::createFromFormat('H:i:s', $data['start']));
        }

        //Should be done from RideType but it returns null...
        if (isset($data['arrival'])) {
            $object->setArrival(DateTime::createFromFormat('H:i:s', $data['arrival']));
        }

        //Converts to boolean
        if (isset($data['locked'])) {
            $object->setLocked((bool) $data['locked']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new Ride();
        $data = $this->mainService->submit($object, 'ride-create', $data);
        $this->addSpecificData($object, $data);

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
        $data = json_decode($data, true);

        foreach ($data as $rideData) {
            //Submits data
            $object = new Ride();
            $this->mainService->submit($object, 'ride-create', $rideData);
            $this->addSpecificData($object, $rideData);

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
     * Returns the list of all rides by date and kind
     * @return array
     */
    public function findAllByDateAndKind(string $date, string $kind)
    {
        return $this->em
            ->getRepository('App:Ride')
            ->findAllByDateAndKind($date, $kind)
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
     * Returns the rides linked to date and driver
     * @return array
     */
    public function findAllByDateByDriver(string $date, $driver)
    {
        return $this->em
            ->getRepository('App:Ride')
            ->findAllByDateByDriver($date, $driver)
        ;
    }

    /**
     * Returns the rides that are linked to another one for date
     * @return array
     */
    public function findAllLinked(string $date)
    {
        return $this->em
            ->getRepository('App:Ride')
            ->findAllLinked($date)
        ;
    }

    /**
     * Returns the ride correspoonding to rideId
     * @return array
     */
    public function findOneById(int $rideId)
    {
        return $this->em
            ->getRepository('App:Ride')
            ->findOneById($rideId)
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
        $this->addSpecificData($object, $data);

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
        if (null !== $object->getDriver() && !$object->getDriver()->getSuppressed()) {
            $objectArray['driver'] = $this->driverService->toArray($object->getDriver());
        }

        //Gets related vehicle
        if (null !== $object->getVehicle() && !$object->getVehicle()->getSuppressed()) {
            $objectArray['vehicle'] = $this->mainService->toArray($object->getVehicle()->toArray());
        }

        //Gets related LinkedRide
        if (null !== $object->getLinkedRide() && !$object->getLinkedRide()->getSuppressed()) {
            $objectArray['linkedRide'] = $this->mainService->toArray($object->getLinkedRide()->toArray());
        }

        //Gets related pickups
        if (null !== $object->getPickups()) {
            $pickups = array();
            $i = 0;
            foreach($object->getPickups() as $pickup) {
                if (!$pickup->getSuppressed()) {
                    $pickups[$i] = $this->mainService->toArray($pickup->toArray());
                    $pickups[$i]['child'] = $this->mainService->toArray($pickup->getChild()->toArray());
                    $i++;
                }
            }
            $objectArray['pickups'] = $pickups;
        }

        return $objectArray;
    }
}

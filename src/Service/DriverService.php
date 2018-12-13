<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Driver;
use App\Entity\DriverZone;
use App\Form\AppFormFactoryInterface;
use App\Service\DriverServiceInterface;

/**
 * DriverService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverService implements DriverServiceInterface
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
    public function addZone(Driver $object, string $postal, int $priority)
    {
        $driverZone = new DriverZone();
        $driverZone
            ->setDriver($object)
            ->setPostal($postal)
            ->setPriority($priority)
        ;

        $this->mainService->create($driverZone);
        $this->mainService->persist($driverZone);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Driver $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'driver-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->create($object);
        $this->mainService->persist($object);

        //Adds links for DriverZones
        if (isset($data['links'])) {
            $links = $data['links'];

            if (null !== $links && is_array($links) && !empty($links)) {
                foreach ($links as $link) {
                    $this->addZone($object, $link['postal'], $link['priority']);
                }
            }
        }

        //Returns data
        return array(
            'status' => true,
            'message' => 'Driver ajouté',
            'driver' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Driver $object)
    {
        //Removes links for zones
        $links = $object->getDriverZones();
        if (null !== $links && !empty($links)) {
            foreach ($links as $link) {
                $this->em->remove($link);
            }
        }

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'Driver supprimé',
        );
    }

    /**
     * Returns the list of all drivers in the array format
     * @return array
     */
    public function findAll()
    {
        return $this->em
            ->getRepository('App:Driver')
            ->findAll()
        ;
    }

    /**
     * Returns the list of all drivers present for the date
     * @return array
     */
    public function findDriversByPresenceDate($date)
    {
        $driversPresence = $this->em
            ->getRepository('App:DriverPresence')
            ->findDriversByPresenceDate($date)
        ;

        $drivers = array();
        foreach ($driversPresence as $driverPresence) {
            $driver = $driverPresence->getDriver();
            $person = $driver->getPerson();
            $drivers[$person->getPersonId()]['name'] = $person->getFirstName() . ' ' . $person->getLastName();
            $drivers[$person->getPersonId()]['vehicle'] = $driver->getVehicle()->getVehicleId();
            foreach ($driver->getDriverZones() as $driverZone) {
                $drivers[$person->getPersonId()][] = $driverZone->getPostal();
            }
        }

        return $drivers;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Driver $object)
    {
        if (null === $object->getPerson()) {
            throw new UnprocessableEntityHttpException('Missing data for Driver -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Driver $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'driver-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Modifies links for driverZones
        if (isset($data['links'])) {
            $links = $data['links'];

            if (null !== $links && is_array($links) && !empty($links)) {
                //Removes existing zones
                foreach ($object->getDriverZones() as $driverZone) {
                    $this->em->remove($driverZone);
                }

                //Adds sublitted zones
                foreach ($links as $link) {
                    $this->addZone($object, $link['postal'], $link['priority']);
                }
            }
        }

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Driver modifié',
            'driver' => $this->toArray($object),
        );
    }

    /**
     * Modifies the priorities for Drivers
     */
    public function priority(string $data)
    {
        //Modifies priorities
        $data = json_decode($data, true);
        if (is_array($data) && !empty($data)) {
            foreach ($data as $driverPriority) {
                $driver = $this->em->getRepository('App:Driver')->findOneById($driverPriority['driver']);

                if ($driver instanceof Driver) {
                    $driver->setPriority($driverPriority['priority']);
                    $this->mainService->modify($driver);
                    $this->mainService->persist($driver);
                }
            }

            //Returns data
            return array(
                'status' => true,
            );
        }

        //Returns data
        return array(
            'status' => false,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Driver $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related person
        if (null !== $object->getPerson()) {
            $objectArray['person'] = $this->mainService->toArray($object->getPerson()->toArray());
        }

        //Gets related vehicle
        if (null !== $object->getVehicle()) {
            $objectArray['vehicle'] = $this->mainService->toArray($object->getVehicle()->toArray());
        }

        //Gets related driverZones
        if (null !== $object->getDriverZones()) {
            $driverZones = array();
            foreach($object->getDriverZones() as $driverZone) {
                $driverZones[] = $this->mainService->toArray($driverZone->toArray());
            }
            $objectArray['driverZones'] = $driverZones;
        }

        return $objectArray;
    }
}

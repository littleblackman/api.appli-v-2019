<?php

namespace App\Service;

use App\Entity\Driver;
use App\Entity\DriverZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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
    public function create(string $data)
    {
        //Submits data
        $object = new Driver();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'driver-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Adds links for DriverZones
        if (array_key_exists('links', $data)) {
            $links = $data['links'];
            if (null !== $links && is_array($links) && !empty($links)) {
                foreach ($links as $link) {
                    $this->addZone($object, $link['postal'], $link['priority']);
                }
            }
        }

        //Persists data
        $this->mainService->persist($object);

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
     * Returns the Driver based on its id
     * @return Driver
     */
    public function findOneById($driverId)
    {
        return $this->em
            ->getRepository('App:Driver')
            ->findOneById($driverId)
        ;
    }

    /**
     * Returns the maxmium number of DriverZones
     * @return int
     */
    public function getMaxDriverZones()
    {
        return $this->em
            ->getRepository('App:DriverZone')
            ->getMaxDriverZones()
        ;
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
        if (array_key_exists('links', $data)) {
            $links = $data['links'];

            if (null !== $links && is_array($links) && !empty($links)) {
                //Removes existing zones
                foreach ($object->getDriverZones() as $driverZone) {
                    $this->em->remove($driverZone);
                }

                //Adds submitted zones
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
                if ($driver instanceof Driver && !$driver->getSuppressed()) {
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
        if (null !== $object->getPerson() && !$object->getPerson()->getSuppressed()) {
            $objectArray['person'] = $this->mainService->toArray($object->getPerson()->toArray());
        }

        //Gets related vehicle
        if (null !== $object->getVehicle() && !$object->getVehicle()->getSuppressed()) {
            $objectArray['vehicle'] = $this->mainService->toArray($object->getVehicle()->toArray());
        }

        //Gets related address
        if (null !== $object->getAddress() && !$object->getAddress()->getSuppressed()) {
            $objectArray['address'] = $this->mainService->toArray($object->getAddress()->toArray());
        }

        //Gets related driverZones
        if (null !== $object->getDriverZones()) {
            $driverZones = array();
            foreach($object->getDriverZones() as $driverZone) {
                if (!$driverZone->getSuppressed()) {
                    $driverZones[] = $this->mainService->toArray($driverZone->toArray());
                }
            }
            $objectArray['driverZones'] = $driverZones;
        }

        return $objectArray;
    }
}

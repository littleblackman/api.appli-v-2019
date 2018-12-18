<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\DriverPresence;
use App\Form\AppFormFactoryInterface;
use App\Service\DriverServiceInterface;
use App\Service\DriverPresenceServiceInterface;

/**
 * DriverPresenceService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverPresenceService implements DriverPresenceServiceInterface
{
    private $em;
    private $driverService;
    private $mainService;

    public function __construct(
        EntityManagerInterface $em,
        DriverServiceInterface $driverService,
        MainServiceInterface $mainService
    )
    {
        $this->em = $em;
        $this->driverService = $driverService;
        $this->mainService = $mainService;
    }

    /**
     * Adds time's data (should be done from RideType but it returns null...)
     */
    public function addTimeData(DriverPresence $object, array $data)
    {
        if (isset($data['start'])) {
            $object->setStart(\DateTime::createFromFormat('H:i:s', $data['start']));
        }
        if (isset($data['end'])) {
            $object->setEnd(\DateTime::createFromFormat('H:i:s', $data['end']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        $data = json_decode($data, true);
        if (is_array($data)) {
            foreach ($data as $driverPresence) {
                $object = $this->em->getRepository('App:DriverPresence')->findByData($driverPresence);
                //Creates object if not already existing
                if (null === $object) {
                    $object = new DriverPresence();
                    //Submits data
                    $this->mainService->submit($object, 'driver-presence-create', $driverPresence);
                    $this->addTimeData($object, $driverPresence);

                    //Checks if entity has been filled
                    $this->isEntityFilled($object);

                    //Persists data
                    $this->mainService->create($object);
                    $this->mainService->persist($object);
                }
            }
        }

        //Returns data
        return array(
            'status' => true,
            'message' => 'DriverPresence ajoutées',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $data)
    {
        $data = json_decode($data, true);
        if (is_array($data)) {
            foreach ($data as $driverPresence) {
                $object = $this->em->getRepository('App:DriverPresence')->findByData($driverPresence);

                //Submits data
                $this->mainService->delete($object);
                $this->mainService->persist($object);
            }
        }

        return array(
            'status' => true,
            'message' => 'DriverPresence supprimées',
        );
    }

    /**
     * Returns the list of all drivers in the array format
     * @return array
     */
    public function findAll()
    {
        return $this->em
            ->getRepository('App:DriverPresence')
            ->findAll()
        ;
    }

    /**
     * Returns the list of presence by driver
     * @return array
     */
    public function findByDriver($driverId, $date)
    {
        return $this->em
            ->getRepository('App:DriverPresence')
            ->findByDriver($driverId, $date)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(DriverPresence $object)
    {
        if (null === $object->getDriver() ||
            null === $object->getDate()) {
            throw new UnprocessableEntityHttpException('Missing data for DriverPresence -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(DriverPresence $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related driver
        if (null !== $object->getDriver()) {
            $objectArray['driver'] = $this->driverService->toArray($object->getDriver());
        }

        return $objectArray;
    }
}

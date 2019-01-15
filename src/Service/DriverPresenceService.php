<?php

namespace App\Service;

use App\Entity\DriverPresence;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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
     * Adds specific data that could not be added via generic method
     */
    public function addSpecificData(DriverPresence $object, array $data)
    {
        //Should be done from DriverPresenceType but it returns null...
        if (isset($data['start'])) {
            $object->setStart(DateTime::createFromFormat('H:i:s', $data['start']));
        }
        if (isset($data['end'])) {
            $object->setEnd(DateTime::createFromFormat('H:i:s', $data['end']));
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
                    $this->addSpecificData($object, $driverPresence);

                    //Checks if entity has been filled
                    $this->isEntityFilled($object);

                    //Persists data
                    $this->mainService->create($object);
                    $this->mainService->persist($object);
                }
            }

            //Returns data
            return array(
                'status' => true,
                'message' => 'DriverPresence ajoutées',
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . json_encode($data));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(DriverPresence $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'DriverPresence supprimée',
        );
    }

    /**
     * Deletes DriverPresence by array of ids
     */
    public function deleteByArray(string $data)
    {
        $data = json_decode($data, true);
        if (is_array($data)) {
            foreach ($data as $driverPresence) {
                $object = $this->em->getRepository('App:DriverPresence')->findByData($driverPresence);

                //Submits data
                $this->mainService->delete($object);
                $this->mainService->persist($object);
            }

            return array(
                'status' => true,
                'message' => 'DriverPresence supprimées',
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . json_encode($data));
    }

    /**
     * Returns the list of all drivers in the array format
     * @return array
     */
    public function findAllByDate($date)
    {
        return $this->em
            ->getRepository('App:DriverPresence')
            ->findAllByDate($date)
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
     * Returns the list of all drivers present for the date
     * @return array
     */
    public function findDriversByPresenceDate($date)
    {
        return $this->em
            ->getRepository('App:DriverPresence')
            ->findDriversByPresenceDate($date)
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
        if (null !== $object->getDriver() && !$object->getDriver()->getSuppressed()) {
            $objectArray['driver'] = $this->driverService->toArray($object->getDriver());
        }

        return $objectArray;
    }
}

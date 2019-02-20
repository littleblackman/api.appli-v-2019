<?php

namespace App\Service;

use App\Entity\DriverZone;
use App\Entity\Staff;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * StaffService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class StaffService implements StaffServiceInterface
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
    public function addZone(Staff $object, string $postal, int $priority)
    {
        $driverZone = new DriverZone();
        $driverZone
            ->setStaff($object)
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
        $object = new Staff();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'staff-create', $data);

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
            'message' => 'Staff ajouté',
            'staff' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Staff $object)
    {
        //Removes links for zones
        if (!$object->getDriverZones()->isEmpty()) {
            foreach ($object->getDriverZones() as $link) {
                $this->em->remove($link);
            }
        }

        //Removes links for groupActivity
        if (!$object->getGroupActivities()->isEmpty()) {
            foreach ($object->getGroupActivities() as $link) {
                $this->em->remove($link);
            }
        }

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'Staff supprimé',
        );
    }

    /**
     * Returns the Staff based on its id
     * @return Staff
     */
    public function findOneById(int $staffId)
    {
        return $this->em
            ->getRepository('App:Staff')
            ->findOneById($staffId)
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
     * Returns the list of all staffs in the array format
     * @return array
     */
    public function findAllByKind(string $kind)
    {
        return $this->em
            ->getRepository('App:Staff')
            ->findAllByKind($kind)
        ;
    }

    /**
     * Returns the list of all staffs in the array format
     * @return array
     */
    public function findAllSearch(string $term)
    {
        return $this->em
            ->getRepository('App:Staff')
            ->findAllSearch($term)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Staff $object)
    {
        if (null === $object->getPerson()) {
            throw new UnprocessableEntityHttpException('Missing data for Staff -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Staff $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'staff-modify', $data);

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
            'message' => 'Staff modifié',
            'staff' => $this->toArray($object),
        );
    }

    /**
     * Modifies the priorities for Staffs
     */
    public function priority(string $data)
    {
        //Modifies priorities
        $data = json_decode($data, true);
        if (is_array($data) && !empty($data)) {
            foreach ($data as $staffPriority) {
                $staff = $this->em->getRepository('App:Staff')->findOneById($staffPriority['staff']);
                if ($staff instanceof Staff && !$staff->getSuppressed()) {
                    $staff->setPriority($staffPriority['priority']);
                    $this->mainService->modify($staff);
                    $this->mainService->persist($staff);
                }
            }

            //Returns data
            return array(
                'status' => true,
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . json_encode($data));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Staff $object)
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

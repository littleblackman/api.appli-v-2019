<?php

namespace App\Service;

use App\Entity\StaffPresence;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * StaffPresenceService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class StaffPresenceService implements StaffPresenceServiceInterface
{
    private $em;

    private $staffService;

    private $mainService;

    public function __construct(
        EntityManagerInterface $em,
        StaffServiceInterface $staffService,
        MainServiceInterface $mainService
    )
    {
        $this->em = $em;
        $this->staffService = $staffService;
        $this->mainService = $mainService;
    }

    /**
     * Adds specific data that could not be added via generic method
     */
    public function addSpecificData(StaffPresence $object, array $data)
    {
        //Should be done from StaffPresenceType but it returns null...
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
        if (is_array($data)) {
            foreach ($data as $staffPresence) {
                $object = $this->em->getRepository('App:StaffPresence')->findByData($staffPresence);
                //Creates object if not already existing
                if (null === $object) {
                    $object = new StaffPresence();
                    $this->mainService->create($object);

                    //Submits data
                    $this->mainService->submit($object, 'staff-presence-create', $staffPresence);
                    $this->addSpecificData($object, $staffPresence);

                    //Checks if entity has been filled
                    $this->isEntityFilled($object);

                    //Persists data
                    $this->mainService->persist($object);
                }
            }

            //Returns data
            return array(
                'status' => true,
                'message' => 'StaffPresence ajoutées',
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . json_encode($data));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(StaffPresence $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'StaffPresence supprimée',
        );
    }

    /**
     * Deletes StaffPresence by array of ids
     */
    public function deleteByArray(string $data)
    {
        $data = json_decode($data, true);
        if (is_array($data)) {
            foreach ($data as $staffPresence) {
                $object = $this->em->getRepository('App:StaffPresence')->findByData($staffPresence);

                //Submits data
                $this->mainService->delete($object);
                $this->mainService->persist($object);
            }

            return array(
                'status' => true,
                'message' => 'StaffPresence supprimées',
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . json_encode($data));
    }

    /**
     * Returns the list of all staffs in the array format
     * @return array
     */
    public function findAllByKindAndDate($kind, $date)
    {
        return $this->em
            ->getRepository('App:StaffPresence')
            ->findAllByKindAndDate($kind, $date)
        ;
    }

    /**
     * Returns the list of presence by staff
     * @return array
     */
    public function findByStaff($staffId, $date)
    {
        return $this->em
            ->getRepository('App:StaffPresence')
            ->findByStaff($staffId, $date)
        ;
    }

    /**
     * Returns the list of all staffs present for the date
     * @return array
     */
    public function findStaffsByPresenceDate($date)
    {
        return $this->em
            ->getRepository('App:StaffPresence')
            ->findStaffsByPresenceDate($date)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(StaffPresence $object)
    {
        if (null === $object->getStaff() ||
            null === $object->getDate()) {
            throw new UnprocessableEntityHttpException('Missing data for StaffPresence -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(StaffPresence $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related staff
        if (null !== $object->getStaff() && !$object->getStaff()->getSuppressed()) {
            $objectArray['staff'] = $this->staffService->toArray($object->getStaff());
        }

        return $objectArray;
    }
}

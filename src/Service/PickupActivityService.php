<?php

namespace App\Service;

use App\Entity\PickupActivity;
use App\Entity\Ride;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * PickupActivityService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PickupActivityService implements PickupActivityServiceInterface
{
    private $em;

    private $mainService;

    private $rideService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService,
        RideServiceInterface $rideService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
        $this->rideService = $rideService;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data, $return = true)
    {
        //Submits data
        $object = new PickupActivity();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'pickup-activity-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->persist($object);

        //Returns data
        if ($return) {
            return array(
                'status' => true,
                'message' => 'PickupActivity ajouté',
                'pickupActivity' => $this->toArray($object),
            );
        }
    }

    /**
     * Creates multiples PickupActivities
     */
    public function createMultiple(string $data)
    {
        $data = json_decode($data, true);
        if (is_array($data) && !empty($data)) {
            foreach ($data as $newPickupActivity) {
                $this->create(json_encode($newPickupActivity), false);
            }

            //Returns data
            return array(
                'status' => true,
                'message' => 'PickupActivities ajoutés',
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . json_encode($data));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(PickupActivity $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'PickupActivity supprimé',
        );
    }

    /**
     * Deletes PickupActivity by registrationId
     */
    public function deleteByRegistrationId(int $registrationId)
    {
        $pickupActivities = $this->em->getRepository('App:PickupActivity')->findByRegistrationId($registrationId);
        if (!empty($pickupActivities)) {
            foreach ($pickupActivities as $pickupActivity) {
                $this->mainService->delete($pickupActivity);
                $this->mainService->persist($pickupActivity);
            }

            return array(
                'status' => true,
                'message' => 'PickupActivity supprimés',
            );
        }
    }

    /**
     * Gets all the PickupActivities by date
     */
    public function findAllByDate(string $date, string $kind)
    {
        return $this->em
            ->getRepository('App:PickupActivity')
            ->findAllByDate($date, $kind)
        ;
    }

    /**
     * Gets all the PickupActivities by status
     */
    public function findAllByStatus(string $date, string $status)
    {
        return $this->em
            ->getRepository('App:PickupActivity')
            ->findAllByStatus($date, $status)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(PickupActivity $object)
    {
        if (null === $object->getStart() ||
            null === $object->getChild()) {
            throw new UnprocessableEntityHttpException('Missing data for PickupActivity -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(PickupActivity $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'pickup-activity-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'PickupActivity modifié',
            'pickupActivity' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(PickupActivity $object)
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

        //Gets related sport
        if (null !== $object->getSport() && !$object->getSport()->getSuppressed()) {
            $objectArray['sport'] = $this->mainService->toArray($object->getSport()->toArray());
        }

        return $objectArray;
    }
}

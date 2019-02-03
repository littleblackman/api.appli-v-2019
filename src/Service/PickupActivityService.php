<?php

namespace App\Service;

use App\Entity\GroupActivity;
use App\Entity\PickupActivity;
use App\Entity\PickupActivityGroupActivityLink;
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
     * Adds link between PickupActivity and GroupActivity
     */
    public function addLink(int $groupActivityId, PickupActivity $object)
    {
        $groupActivity = $this->em->getRepository('App:GroupActivity')->findOneById($groupActivityId);
        if ($groupActivity instanceof GroupActivity && !$groupActivity->getSuppressed()) {
            $pickupActivityGroupActivityLink = new PickupActivityGroupActivityLink();
            $pickupActivityGroupActivityLink
                ->setPickupActivity($object)
                ->setGroupActivity($groupActivity)
            ;
            $this->em->persist($pickupActivityGroupActivityLink);
        }
    }

    /**
     * Adds specific data that could not be added via generic method
     */
    public function addSpecificData(PickupActivity $object, array $data)
    {
        //Should be done from GroupActivityType but it returns null...
        if (array_key_exists('start', $data)) {
            $object->setStart(DateTime::createFromFormat('H:i:s', $data['start']));
        }
        if (array_key_exists('end', $data)) {
            $object->setEnd(DateTime::createFromFormat('H:i:s', $data['end']));
        }

         //Adds links from PickupActivity to GroupActivity
        if (array_key_exists('links', $data)) {
            //Deletes old links
            $oldLinks = $object->getGroupActivities();
            if (null !== $oldLinks && !empty($oldLinks)) {
                foreach ($oldLinks as $oldLink) {
                    $this->em->remove($oldLink);
                }
            }

            //Adds new links
            $links = $data['links'];
            if (null !== $links && is_array($links) && !empty($links)) {
                foreach ($links as $link) {
                    $this->addLink((int) $link['groupActivityId'], $object);
                }
            }
        }
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
        $this->addSpecificData($object, $data);

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
    public function delete(PickupActivity $object, $return = true)
    {
        //Removes links from pickupActivity to groupActivity
        $objectGroupActivityLinks = $this->em->getRepository('App:PickupActivityGroupActivityLink')->findByPickupActivity($object);
        foreach ($objectGroupActivityLinks as $objectGroupActivityLink) {
            if ($objectGroupActivityLink instanceof PickupActivityGroupActivityLink) {
                $this->em->remove($objectGroupActivityLink);
            }
        }

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        if ($return) {
            return array(
                'status' => true,
                'message' => 'PickupActivity supprimé',
            );
        }
    }

    /**
     * Deletes PickupActivity by registrationId
     */
    public function deleteByRegistrationId(int $registrationId)
    {
        $pickupActivities = $this->em->getRepository('App:PickupActivity')->findByRegistrationId($registrationId);
        if (!empty($pickupActivities)) {
            foreach ($pickupActivities as $pickupActivity) {
                $this->delete($pickupActivity, false);
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
        if (null === $object->getDate() ||
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
        $this->addSpecificData($object, $data);

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

        //Gets related groupActivities
        if (null !== $object->getGroupActivities()) {
            $groupActivities = array();
            foreach($object->getGroupActivities() as $groupActivityLink) {
                if (!$groupActivityLink->getGroupActivity()->getSuppressed()) {
                    $groupActivities[] = $this->mainService->toArray($groupActivityLink->getGroupActivity()->toArray());
                }
            }
            $objectArray['groupActivities'] = $groupActivities;
        }

        return $objectArray;
    }
}

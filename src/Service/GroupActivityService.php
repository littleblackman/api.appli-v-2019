<?php

namespace App\Service;

use App\Entity\GroupActivity;
use App\Entity\GroupActivityStaffLink;
use App\Entity\PickupActivity;
use App\Entity\PickupActivityGroupActivityLink;
use App\Entity\Staff;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * GroupActivityService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class GroupActivityService implements GroupActivityServiceInterface
{
    private $em;

    private $mainService;

    private $pickupActivityService;

    private $staffService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService,
        PickupActivityServiceInterface $pickupActivityService,
        StaffServiceInterface $staffService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
        $this->pickupActivityService = $pickupActivityService;
        $this->staffService = $staffService;
    }

    /**
     * Adds link between PickupActivity and GroupActivity
     */
    public function addLink(int $pickupActivityId, GroupActivity $object)
    {
        $pickupActivity = $this->em->getRepository('App:PickupActivity')->findOneById($pickupActivityId);
        if ($pickupActivity instanceof PickupActivity && !$pickupActivity->getSuppressed()) {
            $pickupActivityGroupActivityLink = new PickupActivityGroupActivityLink();
            $pickupActivityGroupActivityLink
                ->setPickupActivity($pickupActivity)
                ->setGroupActivity($object)
            ;
            $this->em->persist($pickupActivityGroupActivityLink);
        }
    }

    /**
     * Adds link betwwen GroupActivity and Staff
     */
    public function addStaff(int $staffId, GroupActivity $object)
    {
        $staff = $this->em->getRepository('App:Staff')->findOneById($staffId);
        if ($staff instanceof Staff && !$staff->getSuppressed()) {
            $groupActivityStaffLink = new GroupActivityStaffLink();
            $groupActivityStaffLink
                ->setGroupActivity($object)
                ->setStaff($staff)
            ;
            $this->em->persist($groupActivityStaffLink);
        }
    }

    /**
     * Adds specific data that could not be added via generic method
     */
    public function addSpecificData(GroupActivity $object, array $data)
    {
        //Should be done from GroupActivityType but it returns null...
        if (array_key_exists('start', $data)) {
            $object->setStart(DateTime::createFromFormat('H:i:s', $data['start']));
        }
        if (array_key_exists('end', $data)) {
            $object->setEnd(DateTime::createFromFormat('H:i:s', $data['end']));
        }

        //Converts to boolean
        if (array_key_exists('lunch', $data)) {
            $object->setLunch((bool) $data['lunch']);
        }

        //Adds links from pickupActivity to groupActivity
        if (array_key_exists('links', $data)) {
            //Deletes old links
            $oldLinks = $object->getPickupActivities();
            if (null !== $oldLinks && !empty($oldLinks)) {
                foreach ($oldLinks as $oldLink) {
                    $this->em->remove($oldLink);
                }
            }

            //Adds new links
            $links = $data['links'];
            if (null !== $links && is_array($links) && !empty($links)) {
                foreach ($links as $link) {
                    $this->addLink((int) $link['pickupActivityId'], $object);
                }
            }
        }

        //Adds links from groupActivity to staff
        if (array_key_exists('staff', $data)) {
            //Deletes old links
            $oldLinks = $object->getStaff();
            if (null !== $oldLinks && !empty($oldLinks)) {
                foreach ($oldLinks as $oldLink) {
                    $this->em->remove($oldLink);
                }
            }

            //Adds new links
            $staff = $data['staff'];
            if (null !== $staff && is_array($staff) && !empty($staff)) {
                foreach ($staff as $staffData) {
                    $this->addStaff((int) $staffData['staffId'], $object);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new GroupActivity();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'group-activity-create', $data);
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'GroupActivity ajouté',
            'groupActivity' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createMultiple(string $data)
    {
        $data = json_decode($data, true);
        if (is_array($data) && !empty($data)) {
            foreach ($data as $groupActivityData) {
                //Submits data
                $object = new GroupActivity();
                $this->mainService->create($object);
                $this->mainService->submit($object, 'group-activity-create', $groupActivityData);
                $this->addSpecificData($object, $groupActivityData);

                //Checks if entity has been filled
                $this->isEntityFilled($object);

                //Persists data
                $this->mainService->persist($object);
            }

            //Returns data
            return array(
                'status' => true,
                'message' => 'GroupActivities ajoutés',
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . json_encode($data));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(GroupActivity $object)
    {
        //Removes links from pickupActivity to groupActivity
        $objectPickupActivityLinks = $this->em->getRepository('App:PickupActivityGroupActivityLink')->findByGroupActivity($object);
        foreach ($objectPickupActivityLinks as $objectPickupActivityLink) {
            if ($objectPickupActivityLink instanceof PickupActivityGroupActivityLink) {
                $this->em->remove($objectPickupActivityLink);
            }
        }

        //Removes links from groupActivity to staff
        $objectStaffLinks = $this->em->getRepository('App:GroupActivityStaffLink')->findByGroupActivity($object);
        foreach ($objectStaffLinks as $objectStaffLink) {
            if ($objectStaffLink instanceof GroupActivityStaffLink) {
                $this->em->remove($objectStaffLink);
            }
        }

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'GroupActivity supprimé',
        );
    }

    /**
     * Returns the list of all groupActivities by date
     * @return array
     */
    public function findAllByDate(string $date)
    {
        return $this->em
            ->getRepository('App:GroupActivity')
            ->findAllByDate($date)
        ;
    }

    /**
     * Returns the GroupActivities linked to date and staff
     * @return array
     */
    public function findAllByDateByStaff(string $date, $staff)
    {
        return $this->em
            ->getRepository('App:GroupActivity')
            ->findAllByDateByStaff($date, $staff)
        ;
    }

    /**
     * Returns the groupActivity correspoonding to groupActivityId
     * @return array
     */
    public function findOneById(int $groupActivityId)
    {
        return $this->em
            ->getRepository('App:GroupActivity')
            ->findOneById($groupActivityId)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(GroupActivity $object)
    {
        if (null === $object->getDate() ||
            null === $object->getName() ||
            null === $object->getStart()) {
            throw new UnprocessableEntityHttpException('Missing data for GroupActivity -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(GroupActivity $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'group-activity-modify', $data);
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'GroupActivity modifié',
            'groupActivity' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(GroupActivity $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related location
        if (null !== $object->getLocation() && !$object->getLocation()->getSuppressed()) {
            $objectArray['location'] = $this->mainService->toArray($object->getLocation()->toArray());
        }

        //Gets related sport
        if (null !== $object->getSport() && !$object->getSport()->getSuppressed()) {
            $objectArray['sport'] = $this->mainService->toArray($object->getSport()->toArray());
        }

        //Gets related pickupActivities
        if (null !== $object->getPickupActivities()) {
            $pickupActivities = array();
            foreach($object->getPickupActivities() as $pickupActivityLink) {
                if (!$pickupActivityLink->getPickupActivity()->getSuppressed()) {
                    $pickupActivities[] = $this->pickupActivityService->toArray($pickupActivityLink->getPickupActivity());
                }
            }
            $objectArray['pickupActivities'] = $pickupActivities;
        }

        //Gets related staff
        if (null !== $object->getStaff()) {
            $staff = array();
            foreach($object->getStaff() as $groupActivityStaffLink) {
                if (!$groupActivityStaffLink->getStaff()->getSuppressed()) {
                    $staff[] = $this->staffService->toArray($groupActivityStaffLink->getStaff());
                }
            }
            $objectArray['staff'] = $staff;
        }

        return $objectArray;
    }
}

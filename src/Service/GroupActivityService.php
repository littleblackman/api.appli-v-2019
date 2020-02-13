<?php

namespace App\Service;

use App\Entity\GroupActivity;
use App\Entity\GroupActivityStaffLink;
use App\Entity\PickupActivity;
use App\Entity\PickupActivityGroupActivityLink;
use App\Entity\Staff;
use App\Entity\StaffPresence;

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


    public function listByLunchGroup($date) {

          $groups = $this->em->getRepository('App:GroupActivity')->findLunchgroup($date);
          $arr = [];
          foreach($groups as $group) {
            $arr[] = $group->getArrayOptimise();
          }
          return $arr;
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
        if (array_key_exists('locked', $data)) {
            $object->setLocked((bool) $data['locked']);
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

    public function duplicateRecursive($source, $target) {

        $debug = [];

        // A retrieve all group from source
        if(!$source_groups = $this->findAllByDate($source)) return ['message' => 'source_no_groups'];

        // B retrieve all activitys on target
        if(!$target_activitys = $this->em->getRepository('App:PickupActivity')->findAllByDate($target)) return ['message' => 'no_target_activities'];

        // C create a table sport / child_id = activity object
        foreach($target_activitys as $a) {
            if($a->getSport()) {
                $activitysArray[$a->getSport()->getSportId()][$a->getChild()->getChildId()] = $a;
            }
        }

        // D List all coach present in target day
        $presenceStaffs =  $this->em->getRepository('App:StaffPresence')->findStaffsByPresenceDate($target);
        foreach($presenceStaffs as $presenceStaff) {
            $staffArray[$presenceStaff->getStaff()->getStaffId()] = $presenceStaff->getStaff();
            $debug[$presenceStaff->getStaff()->getStaffId()] = $presenceStaff->getStaff()->getPerson()->getFirstname().' '.$presenceStaff->getStaff()->getPerson()->getLastname();
        }

        // E duplicate all groups
        foreach($source_groups as $group_s) {
             // create target date object
             $target_date = new DateTime($target);
        
            // add staff if is present in target day
             // check if coach in source is present in target

            if($group_s->getStaff()) {
                $targetStaffArray = null;
                foreach($group_s->getStaff() as $staffLink) {
                    $staff_s = $staffLink->getStaff();

                    // create list staff
                    if(key_exists($staff_s->getStaffId(), $staffArray) && ($staff_s instanceof Staff) )  {
                        $targetStaffArray[] = $staff_s;
                        $person = $staff_s->getPerson();
                        $targetStaffNameArray[] = $person->getFirstname().' '.$person->getLastname();
                        $message['target_staff_present'][] = $person->getFirstname().' '.$person->getLastname();

                      } else {
                          /*
                        $targetStaffArray[] = null;
                        $person = $staff_s->getPerson();
                        $targetStaffNameArray = "driver absent";
                        $message['target_staff_absent'][] = $person->getFirstname().' '.$person->getLastname();*/
                      }
        
                }
            } else {
                $targetStaffArray = null;
            }

            // create target group

            ($group_s->getArea() == null) ? $area = "" : $area = $group_s->getArea();

            $group_t = new GroupActivity();
            $group_t->setDate($target_date);
            $group_t->setName($group_s->getName());
            $group_t->setAge($group_s->getAge());
            $group_t->setStart($group_s->getStart());
            $group_t->setEnd($group_s->getEnd());
            $group_t->setLunch($group_s->getLunch());
            $group_t->setComment($group_s->getComment());
            $group_t->setLocation($group_s->getLocation());
            $group_t->setArea($area);
            $group_t->setSport($group_s->getSport());
            
            $userId = 99;
            $group_t->setCreatedAt(new DateTime());
            $group_t->setCreatedBy($userId);
            $group_t->setUpdatedAt(new DateTime());
            $group_t->setUpdatedBy($userId);
            $group_t->setSuppressed(0);

            $this->em->persist($group_t);
            $this->em->flush();

            // add staff to group
            if($targetStaffArray) {

                foreach($targetStaffArray as $staff) {

                    $debug['targetStaffArray'][] = $staff->getPerson()->getFirstname().' '.$staff->getPerson()->getLastname();
                    
                    if ($staff instanceof Staff) {
                        $linkStaffGroup = new GroupActivityStaffLink();
                        $linkStaffGroup->setGroupActivity($group_t);
                        $linkStaffGroup->setStaff($staff);
                        $this->em->persist($linkStaffGroup);
                        $this->em->flush();
                        $group_t->addStaff($linkStaffGroup, false);
                    }

                   

                }
            }

            $this->em->persist($group_t);
            $this->em->flush();


            // boucle all actvity and check if in table present find the same
            foreach($group_s->getPickupActivities() as $activity_link_s) {

                $activity_s = $activity_link_s->getPickupActivity();
                // add to groups

                if($activity_s->getSport() && \key_exists($activity_s->getChild()->getChildId(), $activitysArray[$activity_s->getSport()->getSportId()])) {
                    // new activity
                    $activity_t = $activitysArray[$activity_s->getSport()->getSportId()][$activity_s->getChild()->getChildId()];
                    $start_time_string = $target.' '.$activity_s->getStart()->format('H:i:s');
                    $activity_t->setStart(new DateTime($start_time_string));
                    $activity_t->setDate($target_date);
                    $activity_t->setChild($activity_s->getChild());
                    $activity_t->setLocation($activity_s->getLocation());
                    $activity_t->setSport($activity_s->getSport());
            

                    $this->em->persist($activity_t);
                    $this->em->flush();

                    // add activity to group
                    $link = new PickupActivityGroupActivityLink();
                    $link->setPickupActivity($activity_t);
                    $link->setGroupActivity($group_t);
                    $this->em->persist($link);
                    $this->em->flush();
                    $group_t->addPickupActivity($link);

                    $message['target_child_associated_to_group'][$activity_s->getChild()->getChildId()] = $activity_s->getChild()->getLastname().' '.$activity_s->getChild()->getFirstname();
    
                    // debug line, delete after debug
 //                   $debug[$debugId][] = $t_pickup->toArray();


                } else {
                    $message['target_child_not_in_target'][$activity_s->getChild()->getChildId()] = $activity_s->getChild()->getLastname().' '.$activity_s->getChild()->getFirstname();

                }


            }

            $this->em->persist($group_t);
            $this->em->flush();
        }


        asort($message['target_child_associated_to_group']);
        asort($message['target_child_not_in_target']);



        return [$debug,$message];
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

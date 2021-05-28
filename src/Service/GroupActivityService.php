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
use PhpCsFixer\Console\Output\NullOutput;
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
    
    public function findByDateBetween($date, $from, $to) {
        $groups = $this->em->getRepository('App:GroupActivity')->findByDateBetween($date, $from, $to);
        
        $new_group = array();
        foreach ($groups as $group) {

            //Gets related pickupActivities
            if (null !== $group->getPickupActivities()) {
                $activitys = array();
                foreach($group->getPickupActivities() as $link) {
                    if (!$link->getPickupActivity()->getSuppressed() && $link->getPickupActivity()->getStatus() != "npec") {
                        $activity = $link->getPickupActivity();
                        $child = $activity->getChild();
                        $activitys[$child->getChildId()] = [ 
                                                                    'firstname' => $child->getFirstname(),
                                                                    'lastname'  => $child->getLastname(),
                                                                    'photo'     => $child->getPhoto()
                        ];
                    }
                }
            }

            //Gets related staff
            if (null !== $group->getStaff()) {
                $staffs = array();
                foreach($group->getStaff() as $link) {
                    if (!$link->getStaff()->getSuppressed()) {
                        $staffs[$link->getStaff()->getStaffId()] = [
                                                                        'name' => $link->getStaff()->getPerson()->getFirstname(),
                                                                        'photo' => $link->getStaff()->getPerson()->getPhoto()
                        ];
                    }
                }
            }
            

            $new_group[] = [
                            'start' => $group->getStart()->format('H:i'),
                            'end'   => $group->getEnd()->format('H:i'),
                            'age'   => $group->getAge(),
                            'lunch' => $group->getLunch(),
                            'location' => $group->getLocation()->getName(),
                            'area'  => $group->getArea(),
                            'staffs' => $staffs,
                            'sport' => $group->getSport()->getName(),
                            'childs' => $activitys
                           
            ];


        };

        return $new_group;
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

    public function duplicateGroup($source, $options = []) {


        (isset($options['target_date'])) ? $target_date = $options['target_date'] : $target_date = $source->getDate();
        ($source->getArea() == null) ? $area = "" : $area = $source->getArea();
        (isset($options['start'])) ? $target_start = $options['start'] : $target_start = $source->getStart();
        (isset($options['end'])) ? $target_end = $options['end'] : $target_end = $source->getEnd();
        (isset($options['isLunch'])) ? $isLunch = $options['isLunch'] : $isLunch = $source->getLunch();

        if($isLunch) {
            $sport = $this->em->getRepository('App:Sport')->find(10); 
        } else {
            $sport = $source->getSport();
        }

        $group_t = new GroupActivity();
        $group_t->setDate($target_date);
        $group_t->setName($source->getName());
        $group_t->setAge($source->getAge());
        $group_t->setStart($target_start);
        $group_t->setEnd($target_end);
        $group_t->setLunch($isLunch);
        $group_t->setComment($source->getComment());
        $group_t->setLocation($source->getLocation());
        $group_t->setArea($area);
        $group_t->setSport($sport);
        
        $userId = 99;
        $group_t->setCreatedAt(new DateTime());
        $group_t->setCreatedBy($userId);
        $group_t->setUpdatedAt(new DateTime());
        $group_t->setUpdatedBy($userId);
        $group_t->setSuppressed(0);

        $this->em->persist($group_t);
        $this->em->flush();


        // copy staff
        foreach($source->getStaff() as $link) {
            $staff = $link->getStaff();

            $linkStaffGroup = new GroupActivityStaffLink();
            $linkStaffGroup->setGroupActivity($group_t);
            $linkStaffGroup->setStaff($staff);

            // persist link
            $this->em->persist($linkStaffGroup);
            $this->em->flush();

            // persist group_t
            $group_t->addStaff($linkStaffGroup, false);
            $this->em->persist($group_t);
            $this->em->flush();
            
        }

            // copy staff
        foreach($source->getPickupActivities() as $link) {
            $activity = $link->getPickupActivity();


            $activityStart = $activity->getStart()->format('Hi');
            $activityEnd   = $activity->getEnd()->format('Hi');

            $groupStart    = $group_t->getStart()->format('Hi');
            $groupEnd      = $group_t->getEnd()->format('Hi');

            // add activity to group_t
            if($activityStart <= $groupStart  && $activityEnd >= $groupEnd) {


                $conditions = [
                                'child' => $activity->getChild(),
                                'sport' => $sport,
                                'date'  => $group_t->getDate()
                ];

                if($lunchActivity = $this->em->getRepository('App:PickupActivity')->findOneBy($conditions)) {

                    $activity = $lunchActivity;

                    $link = new PickupActivityGroupActivityLink();
                    $link->setPickupActivity($activity);
                    $link->setGroupActivity($group_t);

                    // persist ling
                    $this->em->persist($link);
                    $this->em->flush();

                    // persist group_t
                    $group_t->addPickupActivity($link);
                    $this->em->persist($group_t);
                    $this->em->flush();
                }
                
                  
            }
        }

        return $group_t;
    }


    public function duplicateMoment($data) {
        $data  = json_decode($data, true);
        $startDate = new DateTime();
        $el = explode(':', $data['targetMoment']);
        $startDate->setTime(intval($el[0]), intval($el[1]));
        $minEnd = intval($el[1]) + 45;
        $hourEnd = intval($el[0]);
        if($minEnd > 59) {
            $minEnd = $minEnd - 60;
            $hourEnd++;
        }
        $endDate = new DateTime();
        $endDate->setTime($hourEnd, $minEnd);


        if($data['lunch'] == 1) {
            $isLunch = true;
        } else {
            $isLunch = false;
        }


        foreach(json_decode($data['groupsId']) as $group_id) {
            $group = $this->em->getRepository('App:GroupActivity')->find($group_id);
            $target = $this->duplicateGroup($group, ['start' => $startDate, 'end' => $endDate, 'isLunch' => $isLunch]);
        } 

        return [];

    }

    public function duplicateRecursive($source, $target) {

        $debug = []; $message = '';

        $flush = true;

        $margeTime = 70; // 70 <=> 30 minutes

        // A retrieve all group from source
        if(!$source_groups = $this->findAllByDate($source)) return ['message' => 'no_groups_founded_in_source'];

        // A Bis retrieve ALL PA in target day
        if(!$target_activitys = $this->em->getRepository('App:PickupActivity')->findAllByDate($target)) return ['message' => 'no_activity_on_target_day'];

        // create an array with child and all activity create by child (array of child present with an activity)
        foreach($target_activitys as $activity_t) {
            $childPresenceTargets[$activity_t->getChild()->getChildId()][$activity_t->getSport()->getSportId()] = [
                                                                                                                    'start'   => $activity_t->getStart()->format('Hi'),
                                                                                                                    'end'     => $activity_t->getEnd()->format('Hi'),
                                                                                                                    'activity' => $activity_t
                                                                                                             ];
            if(!$flush) {
                $childPresenceTargetsDebug[$activity_t->getChild()->getChildId()][$activity_t->getSport()->getSportId()] = [
                    'start'   => $activity_t->getStart()->format('Hi'),
                    'end'     => $activity_t->getEnd()->format('Hi'),
                    'activity' => $activity_t->toArray()
             ];
             $debug['childPresenceTargets'] = $childPresenceTargetsDebug;  
            }                                                                                                    
        }


        // B List all coach present in target day
        $presenceStaffs =  $this->em->getRepository('App:StaffPresence')->findStaffsByPresenceDate($target);
        foreach($presenceStaffs as $presenceStaff) {
            $staffTarget[$presenceStaff->getStaff()->getStaffId()] = $presenceStaff->getStaff();
            if(!$flush) $debug['staffTarget'][$presenceStaff->getStaff()->getStaffId()] = $presenceStaff->getStaff()->getPerson()->getFirstname().' '.$presenceStaff->getStaff()->getPerson()->getLastname();
        }

        // C create group in target GROUP_TARGET
        foreach($source_groups as $group_s) {

            // create target date object
             $target_date = new DateTime($target);
        
    
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
             if($flush) $this->em->flush();

             $target_groups[$group_s->getgroupActivityId()] = $group_t;
             if(!$flush) $debug['target_group'][$group_s->getgroupActivityId()] = $group_t->toArray();
 
        }
   
        // D Loop on groupe source and retrieve groupe target to update
        foreach($source_groups as $group_s) {

             // group data source
             $sport_id = $group_s->getSport()->getSportId();
             $start    = intval($group_s->getStart()->format('Hi'));
             $end      = intval($group_s->getEnd()->format('Hi'));

             // groupe t associated
             $group_t  = $target_groups[$group_s->getgroupActivityId()];

            // add staff
            if($group_s->getStaff()) {

                foreach($group_s->getStaff() as $staffLink) {
                    $staff_s = $staffLink->getStaff();

                    // check if staff is present on target day
                    if(key_exists($staff_s->getStaffId(), $staffTarget) && ($staff_s instanceof Staff) )  {

                        if ($staff_s instanceof Staff) {
                            $linkStaffGroup = new GroupActivityStaffLink();
                            $linkStaffGroup->setGroupActivity($group_t);
                            $linkStaffGroup->setStaff($staff_s);

                            // persist link
                            $this->em->persist($linkStaffGroup);
                            if($flush) $this->em->flush();
            
                            // persist group_t
                            $group_t->addStaff($linkStaffGroup, false);
                            $this->em->persist($group_t);
                            if($flush) $this->em->flush();
                        }

                    }  else {
                        $messages['staff_not_founded_on_target_day'][] = $staff_s->getPerson()->getFullname();
                    }
        
                }
            }


            // get all acivitiy in group_s
            foreach($group_s->getPickupActivities() as $activity_link_s) {


                // activity source
                $activity_s = $activity_link_s->getPickupActivity();

                // check if child_soruce has an activity_TARGET
                $child_id = $activity_s->getChild()->getChildId();
                if(key_exists($child_id, $childPresenceTargets)) {

                    $childDataTarget = $childPresenceTargets[$child_id];
                    
                    // if child source has an activity in target date
                    if(key_exists($sport_id, $childDataTarget)) {

                        $chilDataActivityTarget = $childDataTarget[$sport_id];


                        // check if group time is beetween child presence activity
                        $childActivityTargetStart = intval($chilDataActivityTarget['start']) - $margeTime;
                        $childActivityTargetEnd   = intval($chilDataActivityTarget['end']) + $margeTime;


                        if($childActivityTargetStart <= $start &&  $end <= $childActivityTargetEnd) {

                            $activity_t = $chilDataActivityTarget['activity'];
                            
                            // add activity to group_t
                            $link = new PickupActivityGroupActivityLink();
                            $link->setPickupActivity($activity_t);
                            $link->setGroupActivity($group_t);

                            // persist ling
                            $this->em->persist($link);
                            if($flush) $this->em->flush();

                            // persist group_t
                            $group_t->addPickupActivity($link);
                            $this->em->persist($group_t);
                            if($flush) $this->em->flush();

                            $messages['child_founded_and_updated'][] = $activity_t->getChild()->getFullnameReverse().' '.$activity_s->getSport()->getName(); 


                        } else {
                            $messages['child_founded_but_presence_is_not_compatible'][] = $activity_s->getChild()->getFullnameReverse().
                                                                                               ' Présence :'.$childActivityTargetStart.'-'.$childActivityTargetEnd.
                                                                                               ' - Groupe : '.$start.'-'.$end;
                        }



                    } else {
                        $messages['child_founded_but_has_not_sport_source_on_target'][] = $activity_s->getChild()->getFullnameReverse().' '.$activity_s->getSport()->getName();
                    }

                } else {
                    $messages['child_not_founded_on_target'][] = $activity_s->getChild()->getFullnameReverse();
                }



            }


        }


        foreach($messages as $key => $datas) {
            sort($datas);
            $arr[$key] = $datas; 
        }

        if(!$flush) {
            $returnData = ['messages' => $arr, 'debug' => $debug];
        }  else {
            $returnData = $arr;
        }
       
        return $returnData;
    }



    public function duplicateRecursiveSAV($source, $target) {

        $debug = [];

        $flush = false;

        // A retrieve all group from source
        if(!$source_groups = $this->findAllByDate($source)) return ['message' => 'source_no_groups'];

        // B retrieve all activitys on target
        if(!$target_activitys = $this->em->getRepository('App:PickupActivity')->findAllByDate($target)) return ['message' => 'no_target_activities'];

        // C create a table sport / child_id = activity object WITH THE TARGET INFORMATION
        foreach($target_activitys as $a) {
            if($a->getSport()) {
                if($a->getChild()->getChildId() == 11538) $activitysArray[$a->getSport()->getSportId()][$a->getChild()->getChildId()] = $a->toArray();
            }
        }

        return [$activitysArray];

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
            if($flush) $this->em->flush();

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
            if($flush) $this->em->flush();


            // boucle all group-actvity target and check if in there is an pickup activty 
            foreach($group_s->getPickupActivities() as $activity_link_s) {

                // activity source
                $activity_s = $activity_link_s->getPickupActivity();
                
                // add to group if the group as the sport and if child is in the acticitiyArray
                if($activity_s->getSport() && \key_exists($activity_s->getChild()->getChildId(), $activitysArray[$activity_s->getSport()->getSportId()])) {
                    
                    // new activity
                    $activity_t = $activitysArray[$activity_s->getSport()->getSportId()][$activity_s->getChild()->getChildId()];


                    // if activty can 

                    //return array($group_s->toArray(), $activity_t->toArray());

                    if($activity_s->getStart() == $activity_t->getStart() && $activity_s->getEnd() == $activity_t->getEnd())
                    {

                        $start_time_string = $target.' '.$activity_s->getStart()->format('H:i:s');
                        $activity_t->setStart(new DateTime($start_time_string));
                        $activity_t->setDate($target_date);
                        $activity_t->setChild($activity_s->getChild());
                        $activity_t->setLocation($activity_s->getLocation());
                        $activity_t->setSport($activity_s->getSport());
                

                        $this->em->persist($activity_t);
                        if($flush) $this->em->flush();

                        // add activity to group
                        $link = new PickupActivityGroupActivityLink();
                        $link->setPickupActivity($activity_t);
                        $link->setGroupActivity($group_t);
                        $this->em->persist($link);
                        $this->em->flush();
                        $group_t->addPickupActivity($link);

                        $message['target_child_associated_to_group'][$activity_s->getChild()->getChildId()] = $activity_s->getChild()->getLastname().' '.$activity_s->getChild()->getFirstname();
        
                    } else {
                        $message['child_founded_but_times_different'][] = $activity_s->getChild()->getLastname().' '.$activity_s->getChild()->getFirstname();
                    }
                    // debug line, delete after debug
 //                   $debug[$debugId][] = $t_pickup->toArray();


                } else {
                    $message['target_child_not_in_target'][$activity_s->getChild()->getChildId()] = $activity_s->getChild()->getLastname().' '.$activity_s->getChild()->getFirstname();

                }


            }

            $this->em->persist($group_t);
            if($flush) $this->em->flush();
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

<?php

namespace App\Service;


use App\Entity\ChildPresence;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Entity\PickupActivity;
use App\Entity\Pickup;
use App\Entity\Meal;
use App\Service\CascadeService;

use DateTimeInterface;

/**
 * ChildPresenceService class.
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ChildPresenceService implements ChildPresenceServiceInterface
{
    private $em;
    private $childService;
    private $mainService;
    private $pickupActivityService;
    private $mealService;
    private $cascadeService;

    public function __construct(
        EntityManagerInterface $em,
        ChildServiceInterface $childService,
        MainServiceInterface $mainService,
        PickupActivityServiceInterface $pickupActivityService,
        MealServiceInterface $mealService,
        CascadeService $cascadeService
    ) {
        $this->em = $em;
        $this->childService = $childService;
        $this->mainService = $mainService;
        $this->pickupActivityService = $pickupActivityService;
        $this->mealService = $mealService;;
        $this->cascadeService = $cascadeService;
    }

    /**
     * Adds specific data that could not be added via generic method.
     */
    public function addSpecificData(ChildPresence $object, array $data)
    {
        //Should be done from ChildPresenceType but it returns null...
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
        if (is_array($data) && !empty($data)) {
            foreach ($data as $childPresence) {
                $object = $this->em->getRepository('App:ChildPresence')->findByData($childPresence);
                //Creates object if not already existing
                if (null === $object) {
                    $object = new ChildPresence();
                    $this->mainService->create($object);

                    //Submits data
                    $this->mainService->submit($object, 'child-presence-create', $childPresence);
                    $this->addSpecificData($object, $childPresence);

                    //Checks if entity has been filled
                    $this->isEntityFilled($object);

                    //Persists data
                    $this->mainService->persist($object);

                    // create Meal from Child Presence
                    $this->createMealFromChildPresence($object);
                }
            }

            //Returns data
            return array(
                'status' => true,
                'message' => 'ChildPresence ajoutées',
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> '.json_encode($data));
    }

    public function createMealFromChildPresence($object) {
        if(!$object->getRegistration()->getHasLunch()) return null;
        return $this->mealService->createMealFromChildPresence($object);
    }


    public function updateLastDayOfWeek($currentDateString) {

        (!$currentDateString) ? $currentDate = date('Y-m-d') : $currentDate = $currentDateString;

        // find the sunday of the week
        $sunday = date("Y-m-d", strtotime("next sunday", strtotime($currentDate)));

        // retrieve all child presence on current day
        $childPresences = $this->em->getRepository(('App:ChildPresence'))->findBy(['date' => new DateTime($currentDate)]);

        if(!$childPresences) return 'no_child_presence_on_'.$currentDate;

        // loop on each child presence
        foreach($childPresences as $presence) {

            // get child 
            $child = $presence->getChild();

            // search child presece >= currentDate and <= sunday of week
            $lastPresence = $this->em->getRepository('App:ChildPresence')->findPresenceBetween($child, $currentDate, $sunday);

            // update lastPresence with the lastDay
            if($lastPresence) {

                // set null to current date
                $presence->setLastDayOfWeek(null);
                $this->em->persist($presence);
                $this->em->flush($presence);


                // set lastPresence the date
                $lastPresence->setLastDayOfWeek($lastPresence->getDate());
                $this->em->persist($lastPresence);
                $this->em->flush($lastPresence);



                /***** update pickup */
                // retrieve pickup from presence and update



                // retrive pickup from lastpresence and update
                

                /***** update pickupactivity */

                if ($pickupActivitys = $this->em->getRepository('App:PickupActivity')->findBy(['child' => $child, 'date' => $presence->getDate()])) {
                    
                    foreach ($pickupActivitys as $pa) {
                    //    $pa->setStatus($status);
                     //   $pa->setStatusChange(new DateTime());
                      //  $this->em->persist($pa);
                       // $this->em->flush();
                    }
                }

                



            }

      
        


        return ['status' => 'last day of week updated for '.$currentDate];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ChildPresence $object, $return = true)
    {
        //cascade
        if($object->getRegistration()) {
            $return = $this->cascadeService->deleteChildPresence($object);
        } 

        $this->mainService->delete($object);
        $this->mainService->persist($object);


        if ($return) {
            return array(
                'status' => true,
                'message' => 'ChildPresence supprimée',
            );
        }
    }

    public function deleteByArrayStringList($idsList) {
        $data = explode(',', $idsList);

        foreach ($data as $childPresence) {
            $childPresence = $this->em->getRepository('App:ChildPresence')->find($childPresence);
            if ($childPresence instanceof ChildPresence) {
                $this->delete($childPresence, false);
            }
        }
        return $data;
    }
    

    /**
     * Deletes ChildPresence by array of ids.
     */
    public function deleteByArray(string $data)
    {

        $data = json_decode($data, true);

        if (is_array($data) && !empty($data)) {
            foreach ($data as $childPresence) {
                $childPresence = $this->em->getRepository('App:ChildPresence')->findByData($childPresence);
                if ($childPresence instanceof ChildPresence) {
                    $this->delete($childPresence, false);
                }
            }

            return array(
                'status' => true,
                'message' => 'ChildPresence supprimées',
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> '.json_encode($data));
    }

    /**
     * Deletes ChildPresence by registrationId.
     */
    public function deleteByRegistrationId(int $registrationId)
    {
        $childPresences = $this->em->getRepository('App:ChildPresence')->findByRegistrationId($registrationId);
        if (!empty($childPresences)) {
            foreach ($childPresences as $childPresence) {
                $this->delete($childPresence, false);
            }

            return array(
                'status' => true,
                'message' => 'ChildPresence supprimées',
            );
        }
    }

    /**
     * Returns the list of all children presence by date.
     *
     * @return array
     */
    public function findAllByDate($date)
    {
        return $this->em
            ->getRepository('App:ChildPresence')
            ->findAllByDate($date)
        ;
    }


    public function findByChildBetweenDates($childId, $from, $to) {

        $child = $this->em->getRepository('App:Child')->find($childId);
        $from = new DateTime($from);
        $to   = new DateTime($to);
        if($childPresences = $this->em->getRepository('App:ChildPresence')->findByChildBetweenDates($child, $from, $to)) {
            foreach($childPresences as $childPresence) {
                $result[$childPresence->getDate()->format('Y-m').'-01'][] = $this->toArray($childPresence);
            }
        } else {
            $result = null;
        }
        
       
        return $result;
    }

    public function findAllWeekPresences($monday)
    {
        $currentDate = new DateTime($monday);
        $presences = [];
        $childPresencesArray = null;

        for($i = 0; $i < 7; $i++) {

            $childPresences = $this->findAllByDate($currentDate->format('Y-m-d'));
            if(!$childPresences) {
                $presences[$currentDate->format('Y-m-d')] = null;
            } else {
                foreach ($childPresences as $childPresence) {
                    if($childPresence->getChild()) {
                        $child = $childPresence->getChild();
                        ($childPresence->getRegistration()) ? $registrationId = $childPresence->getRegistration()->getRegistrationId() : $registrationId = "unknow";
                        if($childPresence->getLastDayOfWeek() != null) {
                            $lastDayOfWeek = $childPresence->getLastDayOfWeek()->format('Y-m-d');
                        } else {
                            $lastDayOfWeek = null;
                        }
                        $childPresencesArray[] = [
                                                    'start' => $childPresence->getStart()->format('H:i:s'),
                                                    'end'   => $childPresence->getEnd()->format('H:i:s'),
                                                    'childId' => $child->getChildId(),
                                                    'urlPhoto' => $child->getPhoto(),
                                                    'firstname' => $child->getFirstname(),
                                                    'lastname'  => $child->getLastname(),
                                                    'childPresenceId' => $childPresence->getChildPresenceId(),
                                                    'registrationid' => $registrationId,
                                                    'lastDayOfWeek' => $lastDayOfWeek

                        ];
                    } else {
                        $childPresencesArray[] = [
                            'childPresenceId' => $childPresence->getChildPresenceId(),
                            'registrationid' => $childPresence->getRegistration()->getRegistrationId()
                        ];
                    }
                 };
                 $presences[$currentDate->format('Y-m-d')] = $childPresencesArray;
                 unset($childPresencesArray);
            }
           
            $currentDate = $currentDate->modify('+1 day');
        }

        return $presences;
    }

    /**
     * Returns the list of presence by child.
     *
     * @return array
     */
    public function findByChild($childId, $date)
    {
        return $this->em
            ->getRepository('App:ChildPresence')
            ->findByChild($childId, $date)
        ;
    }


    public function findByLatestCreated($childId) { 
        return $this->em
        ->getRepository('App:ChildPresence')
        ->findLatestCreatedByChildId($childId)
    ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(ChildPresence $object)
    {
        if (null === $object->getChild() ||
            null === $object->getDate() ||
            null === $object->getStart() ||
            null === $object->getLocation()) {
            throw new UnprocessableEntityHttpException('Missing data for ChildPresence -> '.json_encode($object->toArray()));
        }
    }

    public function updateStatus($child, $date, $status)
    {
        $presence = $this->em->getRepository('App:ChildPresence')->findOneBy(['child' => $child, 'date' => $date]);
        if ($status == null) {
            $status = '';
        }
        $presence->setStatus($status);
        $presence->setStatusChange(new DateTime());

        $this->em->persist($presence);
        $this->em->flush();

        if ($pickupActivitys = $this->em->getRepository('App:PickupActivity')->findBy(['child' => $child, 'date' => $date])) {
            foreach ($pickupActivitys as $pa) {
                $pa->setStatus($status);
                $pa->setStatusChange(new DateTime());
                $this->em->persist($pa);
                $this->em->flush();
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(ChildPresence $object)
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

        //Gets related person
        if (null !== $object->getPerson() && !$object->getPerson()->getSuppressed()) {
            $objectArray['person'] = $this->mainService->toArray($object->getPerson()->toArray());
        }

        //Gets related location
        if (null !== $object->getLocation() && !$object->getLocation()->getSuppressed()) {
            $objectArray['location'] = $this->mainService->toArray($object->getLocation()->toArray());
        }

        return $objectArray;
    }
}

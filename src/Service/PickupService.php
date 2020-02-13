<?php

namespace App\Service;

use App\Entity\Pickup;
use App\Entity\PickupActivity;
use App\Entity\Ride;
Use App\Entity\Child;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * PickupService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PickupService implements PickupServiceInterface
{
    private $em;

    private $staffService;

    private $mainService;

    private $rideService;

    public function __construct(
        EntityManagerInterface $em,
        StaffServiceInterface $staffService,
        MainServiceInterface $mainService,
        RideServiceInterface $rideService
    )
    {
        $this->em = $em;
        $this->staffService = $staffService;
        $this->mainService = $mainService;
        $this->rideService = $rideService;
    }

    /**
     * Affects the Pickups to Rides for a specific date
     */
    public function affect($date, $kind, $force)
    {
        //Unaffects Pickups if force is requested
        if($force) {
            $this->unaffect($date, $kind);
        }

        //Affects all the Pickups
        if ('all' === $kind) {
            //Affect the dropin Pickups
            $this->affectPickups($date, 'dropin');

            //Affect the dropoff Pickups using LinkedRide
            $this->affectPickupsLinkedRide($date);

            //Affect the dropoff Pickups not linked via LinkedRide
            $this->affectPickups($date, 'dropoff');
        //Affects only the specific kind of Pickup
        } else {
            $this->affectPickups($date, $kind);
        }
    }

    /**
     * Affects the Pickups to Rides
     */
    public function affectPickups($date, $kind)
    {
        //Defines Pickups to use
        $pickupsSortOrder = $this->countAllUnaffected($date, $kind);
        $pickups = $this->findAllUnaffected($date, $kind);

        if (!empty($pickups)) {
            //Sorts Pickup by greater number of postal code and builds the array([postal][md5(address)]['pickups'][Pickups]) to group same addresses in same Ride
            $pickupsSorted = array();
            foreach ($pickupsSortOrder as $pickupSortOrder) {
                foreach ($pickups as $pickup) {
                    //Checks address with geocoding
                    $this->checkCoordinates($pickup);
                    //Adds Pickup to its corresponding group of postal code
                    if ($pickupSortOrder['postal'] === $pickup->getPostal()) {
                        $md5 = md5($pickup->getLongitude() . $pickup->getLatitude() . $pickup->getStart()->format('H:i'));

                        $places = 0 === (int) $pickup->getPlaces() ? 1 : (int) $pickup->getPlaces();
                        $places = isset($pickupsSorted[$pickupSortOrder['postal']][$md5]['places']) ? $pickupsSorted[$pickupSortOrder['postal']][$md5]['places'] + $places : $places;
                        $start = isset($pickupsSorted[$pickupSortOrder['postal']][$md5]['start']) ? $pickupsSorted[$pickupSortOrder['postal']][$md5]['start'] : $pickup->getStart();

                        $pickupsSorted[$pickupSortOrder['postal']][$md5]['places'] = $places;
                        $pickupsSorted[$pickupSortOrder['postal']][$md5]['start'] = $start;
                        $pickupsSorted[$pickupSortOrder['postal']][$md5]['pickups'][] = $pickup;
                    }
                }
            }
            unset($pickups);
            unset($pickupsSortOrder);

            //Affects Pickups in the order of postal code and makes as many passes as maxDriverZones
            $rides = $this->rideService->findAllByDateAndKind($date, $kind);
            $maxDriverZones = $this->staffService->getMaxDriverZones() - 1;
            if (null !== $rides) {
                for ($priority = 0; $priority <= $maxDriverZones; $priority++) {
                    foreach ($pickupsSorted as $postal => $pickupsByPostal) {
                        foreach ($pickupsByPostal as $pickups) {
                            $this->affectRide($rides, $pickups, $postal, $priority);
                        }
                    }
                }
            }
        }
    }

    /**
     * Affects all the Pickups that correspond between Ride and LinkedRide
     */
    public function affectPickupLinkedRide($ride)
    {
        $linkedRide = $this->rideService->findOneById($ride->getLinkedRide()->getRideId());
        $linkedRideDateStart = new DateTime($linkedRide->getDate()->format('Y-m-d') . ' ' . $linkedRide->getStart()->format('H:i:s'));
        $linkedRideDateArrival = new DateTime($linkedRide->getDate()->format('Y-m-d') . ' ' . $linkedRide->getArrival()->format('H:i:s'));

        $pickups = $ride->getPickups();
        foreach ($pickups as $pickup) {
            //Affects Pickup to LinkedRide if there is a correponding dropoff for the same child and with is start within the time slot for the Ride
            $pickupDropoff = $this->em->getRepository('App:Pickup')->findOneByDateKindChild($ride->getDate()->format('Y-m-d'), 'dropoff', $pickup->getChild());
            if (null !== $pickupDropoff &&
                $pickupDropoff->getStart() >= $linkedRideDateStart &&
                $pickupDropoff->getStart() <= $linkedRideDateArrival
            ) {
                $pickupDropoff
                    ->setRide($linkedRide)
                    ->setSortOrder($linkedRide->getPickups()->count() + 1)
                    ->setStatus('automatic')
                    ->setStatusChange(new DateTime())
                ;
                $this->mainService->modify($pickupDropoff);
                $this->mainService->persist($pickupDropoff);
                $this->em->refresh($linkedRide);
            }
        }
    }

    /**
     * Affects the Pickups (dropoff) to Rides using LinkedRide
     */
    public function affectPickupsLinkedRide($date)
    {
        $rides = $this->rideService->findAllLinked($date);
        if (null !== $rides) {
            foreach ($rides as $ride) {
                $this->affectPickupLinkedRide($ride);
            }
        }
    }

    /**
     * Affects the Ride to the Pickup
     */
    public function affectRide($rides, $pickups, $postal, $priority)
    {
        //Filters Rides to keep only those that are NOT full
        $rides = array_filter($rides, function($i) {
            $ridePlaces = 0 === (int) $i->getPlaces() ? 8 : (int) $i->getPlaces();
            return (int) $ridePlaces > $i->getOccupiedPlaces() && !$i->getLocked();
        });

        //Filters Pickups to keep only those that are NOT affected
        $pickups['pickups'] = array_filter($pickups['pickups'], function($i) {
            return null === $i->getRide();
        });

        foreach ($rides as $ride) {
            /**
             * Updates Pickup if
             * - Ride is linked to Pickup's postal code using Driver + DriverPriority + DriverZonesPriorities
             * - Ride is not full AND can contain all the Pickups (including places) for the same address
             * - Pickup Start is within Ride's time slot (between start and arrival)
             */
            $rideDateStart = new DateTime($ride->getDate()->format('Y-m-d') . ' ' . $ride->getStart()->format('H:i:s'));
            $rideDateArrival = new DateTime($ride->getDate()->format('Y-m-d') . ' ' . $ride->getArrival()->format('H:i:s'));
            $ridePlaces = 0 === (int) $ride->getPlaces() ? 8 : (int) $ride->getPlaces();

            if (isset($ride->getStaff()->getDriverZones()[$priority]) &&
                null !== $ride->getStaff()->getDriverZones()[$priority] &&
                $postal === (int) $ride->getStaff()->getDriverZones()[$priority]->getPostal() &&
                $pickups['places'] + $ride->getOccupiedPlaces() <= $ridePlaces &&
                $pickups['start'] >= $rideDateStart &&
                $pickups['start'] <= $rideDateArrival
            ) {
                foreach ($pickups['pickups'] as $pickup) {
                    $pickup
                        ->setRide($ride)
                        ->setSortOrder($ride->getPickups()->count() + 1)
                        ->setStatus('automatic')
                        ->setStatusChange(new DateTime())
                    ;
                    $this->mainService->modify($pickup);
                    $this->mainService->persist($pickup);
                    $this->em->refresh($ride);
                }

                break;
            }
        }
    }


    public function updateSmsSentData(string $data)
    {

          $elements = explode(';', $data);

          $pickupId = str_replace('"', '', $elements[0]);
          $number = str_replace('"', '', $elements[1]);

          $pickup = $this->em->getRepository('App:Pickup')->find($pickupId);
          $timeSent = date('Y-m-d H:i');
          $timeSentFr = date('d/m/Y - H:i');
          $data = ['timeSent' => $timeSent, 'number' => $number];

          ($pickup->getSmsSentData() == null) ? $dataArray = [] : $dataArray = $pickup->getSmsSentData();

          $dataArray[] = $data;

          $pickup->setSmsSentData($dataArray);

          $this->em->persist($pickup);
          $this->em->flush();

          //Returns data
          return array(
              'status' => true,
              'message' => 'sms sent data mis à jour',
              'smsSendData' => $pickup->getSmsSentData(),
              'currentDateSent' => $timeSentFr
          );


    }

    /**
     * Checks gps coordinates
     */
    public function checkCoordinates($object, $force = false)
    {
        if ($force ||
            null === $object->getLatitude() ||
            null === $object->getLongitude() ||
            null === $object->getPostal() ||
            5 != strlen($object->getPostal())
        ) {
            $this->mainService->addCoordinates($object);
        }
    }

    /**
     * Gets all the Pickups by date
     */
    public function countAllUnaffected(string $date, string $kind)
    {
        return $this->em
            ->getRepository('App:Pickup')
            ->countAllUnaffected($date, $kind)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data, $return = true)
    {
        //Submits data
        $object = new Pickup();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'pickup-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Checks coordinates
        $this->checkCoordinates($object);

        //Persists data
        $this->mainService->persist($object);

        //Returns data
        if ($return) {
            return array(
                'status' => true,
                'message' => 'Pickup ajouté',
                'pickup' => $this->toArray($object),
            );
        }
    }

    /**
     * Creates multiples Pickups
     */
    public function createMultiple(string $data)
    {
        $data = json_decode($data, true);
        if (is_array($data) && !empty($data)) {
            foreach ($data as $newPickup) {
                $this->create(json_encode($newPickup), false);
            }

            //Returns data
            return array(
                'status' => true,
                'message' => 'Pickups ajoutés',
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . json_encode($data));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Pickup $object, $return = true)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        if ($return) {
            return array(
                'status' => true,
                'message' => 'Pickup supprimé',
            );
        }
    }

    /**
     * Deletes Pickup by registrationId
     */
    public function deleteByRegistrationId(int $registrationId)
    {
        $pickups = $this->em->getRepository('App:Pickup')->findByRegistrationId($registrationId);
        if (!empty($pickups)) {
            foreach ($pickups as $pickup) {
                $this->delete($pickup, false);
            }

            return array(
                'status' => true,
                'message' => 'Pickup supprimés',
            );
        }
    }

    /**
     * Modifies the dispatch for Pickups
     */
    public function dispatch(string $data)
    {
        //Modifies dispatch and sort order
        $data = json_decode($data, true);
        if (is_array($data) && !empty($data)) {
            foreach ($data as $dispatch) {
                $pickup =  $this->em->getRepository('App:Pickup')->findOneById($dispatch['pickupId']);
                if ($pickup instanceof Pickup && !$pickup->getSuppressed()) {
                    //Modifies Ride
                    $ride = $this->em->getRepository('App:Ride')->findOneById($dispatch['rideId']);
                    if ($ride instanceof Ride && !$ride->getSuppressed()) {
                        $pickup->setRide($ride);
                    } elseif (null === $dispatch['rideId'] || 'null' === $dispatch['rideId']) {
                        $pickup->setRide(null);
                    }

                    //Modifies Start
                    if (!empty($dispatch['start'] && '' !== $dispatch['start'])) {
                        $start = DateTime::createFromFormat('Y-m-d H:i:s', $dispatch['start']);
                        if ($start instanceof DateTime) {
                            $pickup->setStart($start);
                        }
                    } elseif (null === $dispatch['start'] || 'null' === $dispatch['start']) {
                        $pickup->setStart(null);
                    }

                    //Modifies other fields
                    $pickup
                        ->setSortOrder($dispatch['sortOrder'])
                        ->setValidated($dispatch['validated'])
                    ;

                    //Persists data
                    $this->mainService->modify($pickup);
                    $this->mainService->persist($pickup);
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
     * Gets all the Pickups by date
     */
    public function findAllByDate(string $date, string $kind)
    {
        return $this->em
            ->getRepository('App:Pickup')
            ->findAllByDate($date, $kind)
        ;
    }

    /**
     * Gets all the Pickups by status
     */
    public function findAllByStatus(string $date, string $status)
    {
        return $this->em
            ->getRepository('App:Pickup')
            ->findAllByStatus($date, $status)
        ;
    }

    /**
     *Gets all the Pickups unaffected
     */
    public function findAllUnaffected(string $date, string $kind)
    {
        return $this->em
            ->getRepository('App:Pickup')
            ->findAllUnaffected($date, $kind)
        ;
    }

    /**
     * Geocodes all the Pickups
     */
    public function geocode()
    {
        $counter = 0;
        $pickups = $this->em
            ->getRepository('App:Pickup')
            ->findGeocode()
        ;
        foreach ($pickups as $pickup) {
            if ($this->mainService->addCoordinates($pickup)) {
                $this->mainService->modify($pickup);
                $this->mainService->persist($pickup);
                $counter++;
            }
        }

        return $counter;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Pickup $object)
    {
        if (null === $object->getStart() ||
            null === $object->getAddress() ||
            null === $object->getChild()) {
            throw new UnprocessableEntityHttpException('Missing data for Pickup -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Pickup $object, string $data)
    {

        $status_original = $object->getStatus();

        //Submits data
        $data = $this->mainService->submit($object, 'pickup-modify', $data);

        //Suppress ride if set to null
        if (array_key_exists('ride', $data) && (null === $data['ride'] || 'null' === $data['ride'])) {
            $object->setRide(null);
        }

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Checks coordinates
        if (array_key_exists('address', $data)) {
            $this->checkCoordinates($object, true);
        }

        if($status_original != $object->getStatus()) {
            $object->setStatusChange(new DateTime());
        }

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        $this->updatePickupActivity($object);


        //Returns data
        return array(
            'status' => true,
            'message' => 'Pickup modifié',
            'pickup' => $this->toArray($object),
        );
    }

    public function updatePickupActivity($pickup) {

    //  if(!$activity = $this->em->getRepository('App:PickupActivity')->findBy(['child' => $pickup->getChild(), 'date' => $pickup->getDate()])) return null;
    //  $activity->setStatus($pickup->getStatus());


      //$this->mainService->modify($activity);
      //$this->mainService->persist($activity);

    }

  /**
   * {@inheritdoc}
   */
   public function getLastPEC($childId, $kind)
   {

        $child = $this->em->getRepository('App:Child')->find($childId);
        $pickup = $this->em
                ->getRepository('App:Pickup')->findOneBy(
                                                    array('child' => $child, 'kind' => $kind, 'status' => 'pec'),
                                                    array('start' => 'desc'),
                                                    1
                                                );
        if(!$pickup) return null;
        $result = [
                                'kind' => $pickup->getKind(),
                                'status_change' => $pickup->getStatusChange(),
                                'start' => $pickup->getStart(),
                                'status' => $pickup->getStatus()
                    ];
        return $result;
   }


    /**
     * {@inheritdoc}
     */
    public function toArray(Pickup $object)
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

        //Gets related ride
        if (null !== $object->getRide() && !$object->getRide()->getSuppressed()) {
            $objectArray['ride'] = $this->mainService->toArray($object->getRide()->toArray());
        }

        return $objectArray;
    }

    /**
     * Unaffects all the pickups from rides for a specific date
     */
    public function unaffect($date, $kind)
    {
        $counter = 0;
        $kinds = 'all' === $kind ? array('dropin','dropoff') : array($kind);
        foreach ($kinds as $kind) {
            $pickups = $this->findAllByDate($date, $kind);
            if (!empty($pickups)) {
                foreach ($pickups as $pickup) {
                    //Unaffects Pïckup if it's Ride is NOT locked
                    if (null !== $pickup->getRide() && !$pickup->getRide()->getLocked()) {
                        $pickup
                            ->setRide(null)
                            ->setSortOrder(null)
                            ->setStatus(null)
                            ->setStatusChange(new DateTime())
                        ;
                        $this->mainService->modify($pickup);

                        $counter++;
                        if (20 === $counter) {
                            $this->em->flush();
                            $counter = 0;
                        }
                    }
                }
            }
        }

        $this->em->flush();
    }
}

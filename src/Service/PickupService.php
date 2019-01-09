<?php

namespace App\Service;

use App\Entity\Pickup;
use App\Entity\Ride;
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

    private $driverService;

    private $mainService;

    private $rideService;

    public function __construct(
        EntityManagerInterface $em,
        DriverServiceInterface $driverService,
        MainServiceInterface $mainService,
        RideServiceInterface $rideService
    )
    {
        $this->em = $em;
        $this->driverService = $driverService;
        $this->mainService = $mainService;
        $this->rideService = $rideService;
    }

    /**
     * Affects all the Pickups to Rides for a specific date
     */
    public function affect($date, $kind, $force)
    {
        //Defines Pickups to use
        if ($force) {
            $pickupsSortOrder = $this->countAllByDate($date, $kind);
            $pickups = $this->findAllByDate($date, $kind);
        } else {
            $pickupsSortOrder = $this->countAllUnaffected($date, $kind);
            $pickups = $this->findAllUnaffected($date, $kind);
        }

        if (!empty($pickups)) {
            //Sorts Pickup by greater number of postal code and builds the array([postal][md5(address)]['pickups'][Pickups]) to group same addresses in same Ride
            $pickupsSorted = array();
            foreach ($pickupsSortOrder as $pickupSortOrder) {
                foreach ($pickups as $pickup) {
                    //Checks address with geocoding
                    $this->checkCoordinates($pickup);
                    //Adds Pickup to its corresponding group of postal code
                    if ($pickupSortOrder['postal'] === $pickup->getPostal()) {
                        $md5 = md5($pickup->getLongitude() . $pickup->getLatitude());

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
            $maxDriverZones = $this->driverService->getMaxDriverZones() - 1;
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
            if (isset($ride->getDriver()->getDriverZones()[$priority]) &&
                $postal === (int) $ride->getDriver()->getDriverZones()[$priority]->getPostal() &&
                $pickups['places'] + $ride->getOccupiedPlaces() <= $ridePlaces &&
                $pickups['start'] >= $rideDateStart && $pickups['start'] <= $rideDateArrival) {
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
    public function countAllByDate(string $date, string $kind)
    {
        return $this->em
            ->getRepository('App:Pickup')
            ->countAllByDate($date, $kind)
        ;
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
    public function create(string $data)
    {
        //Submits data
        $object = new Pickup();
        $data = $this->mainService->submit($object, 'pickup-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Checks coordinates
        $this->checkCoordinates($object);

        //Persists data
        $this->mainService->create($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Pickup ajouté',
            'pickup' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Pickup $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'Pickup supprimé',
        );
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
                if ($pickup instanceof Pickup) {
                    //Modifies Ride
                    $ride = $this->em->getRepository('App:Ride')->findOneById($dispatch['rideId']);
                    if ($ride instanceof Ride) {
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

        //Returns data
        return array(
            'status' => false,
        );
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
        $counterRecords = 0;
        $pickups = $this->em
            ->getRepository('App:Pickup')
            ->findGeocode()
        ;
        foreach ($pickups as $pickup) {
            if ($this->mainService->addCoordinates($pickup)) {
                $this->mainService->modify($pickup);
                $this->mainService->persist($pickup);
                $counterRecords++;
            }
        }

        return $counterRecords;
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
        //Submits data
        $data = $this->mainService->submit($object, 'pickup-modify', $data);

        //Suppress ride if set to null
        if (array_key_exists('ride', $data) && (null === $data['ride'] || 'null' === $data['ride'])) {
            $object->setRide(null);
        }

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Checks coordinates
        if (isset($data['address'])) {
            $this->checkCoordinates($object, true);
        }

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Pickup modifié',
            'pickup' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Pickup $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

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
     * Unaffects all the pickups to rides and drivers for a specific date
     */
    public function unaffect($date, $kind)
    {
        $pickups = $this->findAllByDate($date, $kind);
        if (!empty($pickups)) {
            $counter = 0;
            foreach ($pickups as $pickup) {
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
                }
            }

            $this->em->flush();
        }
    }
}

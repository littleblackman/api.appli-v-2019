<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Entity\Pickup;
use App\Entity\Ride;
use App\Service\DriverServiceInterface;
use App\Service\DriverPresenceServiceInterface;
use App\Service\PickupServiceInterface;
use App\Service\RideServiceInterface;

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

    const RIDE_FULL = 8;

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
    public function affect($date, $force)
    {
        //Defines Pickups to use
        if ($force) {
            $pickupsSortOrder = $this->em->getRepository('App:Pickup')->countAllByDate($date);
            $pickups = $this->findAllByDate($date);
        } else {
            $pickupsSortOrder = $this->em->getRepository('App:Pickup')->countAllUnaffected($date);
            $pickups = $this->findAllUnaffected($date);
        }

        if (!empty($pickups)) {
            //Sorts Pickup by greater number of postal code
            $pickupsSorted = array();
            foreach ($pickupsSortOrder as $pickupSortOrder) {
                foreach ($pickups as $pickup) {
                    if ($pickupSortOrder['postal'] === $pickup->getPostal()) {
                        $pickupsSorted[$pickupSortOrder['postal']][] = $pickup;
                    }
                }
            }
            unset($pickups);
            unset($pickupsSortOrder);

            //Affects Pickups in the order of postal code and makes as many passes as maxDriverZones
            $rides = $this->rideService->findAllByDate($date);
            $maxDriverZones = 5;
            for ($priority = 0; $priority <= $maxDriverZones; $priority++) {
                foreach ($pickupsSorted as $pickups) {
                    foreach ($pickups as $pickup) {
                        $this->affectRide($rides, $pickup, $priority);
                    }
                }
            }
        }
    }

    /**
     * Affects the Ride to the Pickup
     */
    public function affectRide($rides, $pickup, $priority)
    {
        //Filters Rides to keep only those that are NOT full
        $rides = array_filter($rides, function($i) {
            return self::RIDE_FULL > $i->getPickups()->count();
        });

        foreach ($rides as $ride) {
            /**
             * Updates Pickup if Ride is
             * - linked to its postal code using Driver > DriverZones + Priority
             * - corresponding to its time slot
             */
            if (isset($ride->getDriver()->getDriverZones()[$priority]) &&
                $pickup->getPostal() === $ride->getDriver()->getDriverZones()[$priority]->getPostal() &&
                $pickup->getStart() >= $ride->getStart()) {
                $pickup
                    ->setRide($ride)
                    ->setSortOrder($ride->getPickups()->count() + 1)
                    ->setStatus('automatic')
                    ->setStatusChange(new \DateTime())
                ;
                $this->mainService->modify($pickup);
                $this->mainService->persist($pickup);
                $this->em->refresh($ride);

                break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(Pickup $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'pickup-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

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
                        $start = \DateTime::createFromFormat('Y-m-d H:i:s', $dispatch['start']);
                        if ($start instanceof \DateTime) {
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
    public function findAllByDate(string $date)
    {
        return $this->em
            ->getRepository('App:Pickup')
            ->findAllByDate($date)
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
    public function findAllUnaffected(string $date)
    {
        return $this->em
            ->getRepository('App:Pickup')
            ->findAllUnaffected($date)
        ;
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

        //Suppress ride
        if (array_key_exists('ride', $data) && (null === $data['ride'] || 'null' === $data['ride'])) {
            $object->setRide(null);
        }

        //Checks if entity has been filled
        $this->isEntityFilled($object);

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
        if (null !== $object->getChild()) {
            $objectArray['child'] = $this->mainService->toArray($object->getChild()->toArray());
        }

        //Gets related ride
        if (null !== $object->getRide()) {
            $objectArray['ride'] = $this->mainService->toArray($object->getRide()->toArray());
        }

        return $objectArray;
    }

    /**
     * Unaffects all the pickups to rides and drivers for a specific date
     */
    public function unaffect($date)
    {
        $pickups = $this->findAllByDate($date);
        if (!empty($pickups)) {
            $counter = 0;
            foreach ($pickups as $pickup) {
                $pickup
                    ->setRide(null)
                    ->setSortOrder(null)
                    ->setStatus(null)
                    ->setStatusChange(new \DateTime())
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

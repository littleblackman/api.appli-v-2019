<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Entity\Pickup;
use App\Entity\Ride;
use App\Service\DriverServiceInterface;
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
     * Affects all the pickups to rides and drivers for a specific date
     */
    public function affect($date, $force)
    {
        $pickups = $force ? $this->findAllByDate($date) : $this->findAllUnaffected($date);
        if (!empty($pickups)) {
            $drivers = $this->driverService->findDriversByPresenceDate($date);

            //Creates missing Rides
            $this->rideService->createMissingByDate($date, $drivers);

            //Affects Pickup to Ride
            $updateRides = false;
            foreach ($pickups as $pickup) {
                foreach ($drivers as $key => $driver) {
                    $driverZone = array_search($pickup->getPostal(), $driver);
                    if (is_int($driverZone)) {
                        //Gets Ride related to driver
                        $ride = $this->rideService->findOneByDateByPersonId($date, $key);

                        //Updates Pickup if the Ride is not full
                        $counterPickups = $ride->getPickups()->count();
                        if ($counterPickups < self::RIDE_FULL) {
                            $updateRides = true;
                            $pickup
                                ->setRide($ride)
                                ->setSortOrder($counterPickups + 1)
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
            }

            //Updates Rides
            if ($updateRides) {
                $rides = $this->rideService->findAllByDate($date);
                foreach ($rides as $ride) {
                    $firstPickup = $ride->getPickups()->first();
                    if ($firstPickup instanceof Pickup) {
                        $ride
                            ->setStartPoint($firstPickup->getAddress())
                            ->setStart($firstPickup->getStart())
                        ;
                    }
                    $lastPickup = $ride->getPickups()->last();
                    if ($lastPickup instanceof Pickup) {
                        $ride
                            ->setEndPoint($lastPickup->getAddress())
                            ->setArrival($lastPickup->getStart())
                        ;
                    }

                    $this->mainService->modify($ride);
                    $this->mainService->persist($ride);
                }
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

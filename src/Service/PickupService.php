<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Entity\Pickup;
use App\Entity\Ride;
use App\Service\PickupServiceInterface;

/**
 * PickupService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PickupService implements PickupServiceInterface
{
    private $em;
    private $mainService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
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
     * {@inheritdoc}
     */
    public function findAllByStatus(string $date, string $status)
    {
        return $this->em
            ->getRepository('App:Pickup')
            ->findAllByStatus($date, $status)
        ;
    }

    /**
     * {@inheritdoc}
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
}

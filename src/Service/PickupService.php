<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Entity\Pickup;
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
        if (array_key_exists('ride', $data) && null === $data['ride']) {
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
}

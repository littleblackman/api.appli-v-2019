<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Entity\Week;
use App\Service\WeekServiceInterface;

/**
 * WeekService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class WeekService implements WeekServiceInterface
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
    public function create(Week $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'week-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->create($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Semaine ajoutée',
            'week' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Week $object, string $data)
    {
        $data = json_decode($data, true);

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'Semaine supprimée',
        );
    }

    /**
     * Returns the list of all weeks in the array format
     * @return array
     */
    public function findAll()
    {
        return $this->em
            ->getRepository('App:Week')
            ->findAll()
        ;
    }

    /**
     * Searches the term in the Week collection
     * @return array
     */
    public function findAllSearch(string $term)
    {
        return $this->em
            ->getRepository('App:Week')
            ->findAllSearch($term)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Week $object)
    {
        if (null === $object->getKind() ||
            null === $object->getName() ||
            null === $object->getDateStart()) {
            throw new UnprocessableEntityHttpException('Missing data for Week -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Week $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'week-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Semaine modifiée',
            'week' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Week $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related season
        if (null !== $object->getSeason()) {
            $objectArray['season'] = $this->mainService->toArray($object->getSeason()->toArray());
        }

        return $objectArray;
    }
}

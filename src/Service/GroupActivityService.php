<?php

namespace App\Service;

use App\Entity\Person;
use App\Entity\GroupActivity;
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

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
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
            null === $object->getName() ||
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

        return $objectArray;
    }
}

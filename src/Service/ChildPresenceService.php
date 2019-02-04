<?php

namespace App\Service;

use App\Entity\ChildPresence;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * ChildPresenceService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ChildPresenceService implements ChildPresenceServiceInterface
{
    private $em;

    private $childService;

    private $mainService;

    public function __construct(
        EntityManagerInterface $em,
        ChildServiceInterface $childService,
        MainServiceInterface $mainService
    )
    {
        $this->em = $em;
        $this->childService = $childService;
        $this->mainService = $mainService;
    }

    /**
     * Adds specific data that could not be added via generic method
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
                }
            }

            //Returns data
            return array(
                'status' => true,
                'message' => 'ChildPresence ajoutées',
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . json_encode($data));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ChildPresence $object, $return = true)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        if ($return) {
            return array(
                'status' => true,
                'message' => 'ChildPresence supprimée',
            );
        }
    }

    /**
     * Deletes ChildPresence by array of ids
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

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . json_encode($data));
    }

    /**
     * Deletes ChildPresence by registrationId
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
     * Returns the list of all children presence by date
     * @return array
     */
    public function findAllByDate($date)
    {
        return $this->em
            ->getRepository('App:ChildPresence')
            ->findAllByDate($date)
        ;
    }

    /**
     * Returns the list of presence by child
     * @return array
     */
    public function findByChild($childId, $date)
    {
        return $this->em
            ->getRepository('App:ChildPresence')
            ->findByChild($childId, $date)
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
            throw new UnprocessableEntityHttpException('Missing data for ChildPresence -> ' . json_encode($object->toArray()));
        }
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

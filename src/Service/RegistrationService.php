<?php

namespace App\Service;

use App\Entity\Registration;
use App\Entity\RegistrationSportLink;
use App\Entity\Sport;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * RegistrationService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class RegistrationService implements RegistrationServiceInterface
{
    private $em;

    private $mainService;

    private $personService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService,
        PersonServiceInterface $personService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
        $this->personService = $personService;
    }

    /**
     * Adds specific data that could not be added via generic method
     */
    public function addSpecificData(Registration $object, array $data)
    {
        //Adds registration datetime
        $object->setRegistration(new DateTime());

        //Adds preferences
        if (array_key_exists('preferences', $data)) {
            $object->setPreferences(serialize($data['preferences']));
        }

        //Adds sessions
        if (array_key_exists('sessions', $data)) {
            $object->setSessions(serialize($data['sessions']));
        }

        //Adds sports
        if (array_key_exists('sports', $data)) {
            //Removes old links
            $this->removeSportsLinks($object);

            //Adds new links
            foreach ($data['sports'] as $sport) {
                $this->addSportLink($sport['sportId'], $object);
            }
        }
    }

    /**
     * Adds link between Registration and Sport
     */
    public function addSportLink(int $sportId, Registration $object)
    {
        $sport = $this->em->getRepository('App:Sport')->findOneById($sportId);
        if ($sport instanceof Sport && !$sport->getSuppressed()) {
            $registrationSportLink = new RegistrationSportLink();
            $registrationSportLink
                ->setRegistration($object)
                ->setSport($sport)
            ;
            $this->em->persist($registrationSportLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new Registration();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'registration-create', $data);
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Inscription ajoutée',
            'registration' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Registration $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        //Deletes links to sports
        $this->removeSportsLinks($object);

        return array(
            'status' => true,
            'message' => 'Inscription supprimée',
        );
    }

    /**
     * Returns the list of all registrations related to status in the array format
     */
    public function findAllByStatus($status)
    {
        return $this->em
            ->getRepository('App:Registration')
            ->findAllByStatus($status)
        ;
    }

    /**
     * Returns the list of all registrations related to person and status in the array format
     */
    public function findAllByPersonAndStatus($personId, $status)
    {
        return $this->em
            ->getRepository('App:Registration')
            ->findAllByPersonAndStatus($personId, $status)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Registration $object)
    {
        if (null === $object->getChild() ||
            null === $object->getProduct()) {
            throw new UnprocessableEntityHttpException('Missing data for Registration -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Registration $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'registration-modify', $data);
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Inscription modifiée',
            'registration' => $this->toArray($object),
        );
    }

    /**
     * Removes links from Registration
     */
    public function removeSportsLinks(Registration $object)
    {
        $links = $object->getSports();
        if (null !== $links && !empty($links)) {
            foreach ($links as $link) {
                $this->em->remove($link);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Registration $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related child
        if (null !== $object->getChild() && !$object->getChild()->getSuppressed()) {
            $objectArray['child'] = $this->mainService->toArray($object->getChild()->toArray());
        }

        //Gets related person
        if (null !== $object->getPerson() && !$object->getPerson()->getSuppressed()) {
            $objectArray['person'] = $this->mainService->toArray($object->getPerson()->toArray());
        }

        //Gets related product
        if (null !== $object->getProduct() && !$object->getProduct()->getSuppressed()) {
            $objectArray['product'] = $this->mainService->toArray($object->getProduct()->toArray());
        }

        //Gets related location
        if (null !== $object->getLocation() && !$object->getLocation()->getSuppressed()) {
            $objectArray['location'] = $this->mainService->toArray($object->getLocation()->toArray());
        }

        //Gets related sports
        if (null !== $object->getSports()) {
            $sports = array();
            foreach($object->getSports() as $sport) {
                if (!$sport->getSport()->getSuppressed()) {
                    $sports[] = $this->mainService->toArray($sport->getSport()->toArray());
                }
            }
            $objectArray['sports'] = $sports;
        }

        return $objectArray;
    }
}

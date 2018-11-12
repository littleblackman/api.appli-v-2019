<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Person;
use App\Entity\Ride;
use App\Entity\UserPersonLink;
use App\Form\AppFormFactoryInterface;
use App\Service\PersonServiceInterface;

/**
 * RideService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class RideService implements RideServiceInterface
{
    private $personService;
    private $em;
    private $formFactory;
    private $security;
    private $user;

    public function __construct(
        PersonServiceInterface $personService,
        EntityManagerInterface $em,
        AppFormFactoryInterface $formFactory,
        Security $security,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->personService = $personService;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->security = $security;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function create(Ride $ride, string $data)
    {
        $data = json_decode($data, true);
        $form = $this->formFactory->create('ride-create', $ride);
        $form->submit($data);

        //Adds time's data (should be done from RideType but it returns null...)
        $ride
            ->setStart(\DateTime::createFromFormat('H:i:s', $data['start']))
            ->setArrival(\DateTime::createFromFormat('H:i:s', $data['arrival']))
            ;

        //Checks if entity has been filled
        $this->isEntityFilled($ride);

        //Adds data
        $ride
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($this->user->getId())
            ->setSuppressed(false)
        ;
        $this->em->persist($ride);

        //Persists in DB
        $this->em->flush();
        $this->em->refresh($ride);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Trajet ajouté',
            'ride' => $this->filter($ride->toArray()),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Ride $ride)
    {
        $ride
            ->setSuppressed(true)
            ->setSuppressedAt(new \DateTime())
            ->setSuppressedBy($this->user->getId())
        ;
        $this->em->persist($ride);

        //Puts all pickups as "Non pris en charge"
/*        $childPersonLinks = $this->em->getRepository('App:ChildPersonLink')->findByPerson($person);
        foreach ($childPersonLinks as $childPersonLink) {
            if ($childPersonLink instanceof ChildPersonLink) {
                $this->em->remove($childPersonLink);
            }
        }*/

        //Persists in DB
        $this->em->flush();

        return array(
            'status' => true,
            'message' => 'Trajet supprimé',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $rideArray)
    {
        //Global data
        $globalData = array(
            '__initializer__',
            '__cloner__',
            '__isInitialized__',
        );

        //User's role linked data
        $specificData = array();
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $specificData = array_merge(
                $specificData,
                array(
                    'createdAt',
                    'createdBy',
                    'updatedAt',
                    'updatedBy',
                    'suppressed',
                    'suppressedAt',
                    'suppressedBy',
                )
            );
        }

        //Deletes unwanted data
        foreach (array_merge($globalData, $specificData) as $unsetData) {
            unset($rideArray[$unsetData]);
        }

        //Filters person
        if (isset($rideArray['person']) && is_array($rideArray['person'])) {
            $rideArray['person'] = $this->personService->filter($rideArray['person']);
        }

        return $rideArray;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByDate(string $date)
    {
        return $this->em
            ->getRepository('App:Ride')
            ->findAllByDate($date)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllInArray(string $status)
    {
        return $this->em
            ->getRepository('App:Ride')
            ->findAllInArray($status)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByDateByPersonId(string $date, Person $person)
    {
        return $this->em
            ->getRepository('App:Ride')
            ->findOneByDateByPersonId($date, $person)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Ride $ride)
    {
        if (null === $ride->getDate() ||
            null === $ride->getName() ||
            null === $ride->getStart() ||
            null === $ride->getArrival() ||
            null === $ride->getStartPoint() ||
            null === $ride->getEndPoint()) {
            throw new UnprocessableEntityHttpException('Missing data for Ride -> ' . json_encode($ride->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Ride $ride, string $data)
    {
        $data = json_decode($data, true);
        $form = $this->formFactory->create('ride-modify', $ride);
        $form->submit($data);

        //Adds time's data (should be done from RideType but it returns null...)
        $ride
            ->setStart(\DateTime::createFromFormat('H:i:s', $data['start']))
            ->setArrival(\DateTime::createFromFormat('H:i:s', $data['arrival']))
            ;

        //Checks if entity has been filled
        $this->isEntityFilled($ride);

        //Adds data
        $ride
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->user->getId())
        ;

        //Persists in DB
        $this->em->persist($ride);
        $this->em->flush();
        $this->em->refresh($ride);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Trajet modifié',
            'ride' => $this->filter($ride->toArray()),
        );
    }
}

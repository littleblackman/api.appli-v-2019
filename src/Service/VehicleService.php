<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Vehicle;
use App\Entity\UserPersonLink;
use App\Form\AppFormFactoryInterface;

/**
 * VehicleService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class VehicleService implements VehicleServiceInterface
{
    private $em;
    private $formFactory;
    private $security;
    private $user;

    public function __construct(
        EntityManagerInterface $em,
        AppFormFactoryInterface $formFactory,
        Security $security,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->security = $security;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function create(Vehicle $vehicle, string $data)
    {
        $data = json_decode($data, true);
        $form = $this->formFactory->create('vehicle-create', $vehicle);
        $form->submit($data);

        //Checks if entity has been filled
        $this->isEntityFilled($vehicle);

        //Adds data
        $vehicle
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($this->user->getId())
            ->setSuppressed(false)
        ;
        $this->em->persist($vehicle);

        //Persists in DB
        $this->em->flush();
        $this->em->refresh($vehicle);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Véhicule ajouté',
            'vehicle' => $this->filter($vehicle->toArray()),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Vehicle $vehicle)
    {
        $vehicle
            ->setSuppressed(true)
            ->setSuppressedAt(new \DateTime())
            ->setSuppressedBy($this->user->getId())
        ;
        $this->em->persist($vehicle);

        //Persists in DB
        $this->em->flush();

        return array(
            'status' => true,
            'message' => 'Véhicule supprimé',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $vehicleArray)
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
            unset($vehicleArray[$unsetData]);
        }

        return $vehicleArray;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllInArray()
    {
        return $this->em
            ->getRepository('App:Vehicle')
            ->findAllInArray()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Vehicle $vehicle)
    {
        if (null === $vehicle->getName() ||
            null === $vehicle->getMatriculation() ||
            null === $vehicle->getCombustible()) {
            throw new UnprocessableEntityHttpException('Missing data for Vehicle -> ' . json_encode($vehicle->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Vehicle $vehicle, string $data)
    {
        $data = json_decode($data, true);
        $form = $this->formFactory->create('vehicle-modify', $vehicle);
        $form->submit($data);

        //Checks if entity has been filled
        $this->isEntityFilled($vehicle);

        //Adds data
        $vehicle
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->user->getId())
        ;

        //Persists in DB
        $this->em->persist($vehicle);
        $this->em->flush();
        $this->em->refresh($vehicle);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Véhicule modifié',
            'vehicle' => $this->filter($vehicle->toArray()),
        );
    }
}

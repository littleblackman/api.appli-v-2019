<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Season;
use App\Entity\SeasonPersonLink;
use App\Entity\Person;
use App\Form\AppFormFactoryInterface;
use App\Service\SeasonServiceInterface;

/**
 * SeasonService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class SeasonService implements SeasonServiceInterface
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
    public function create(Season $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'season-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->create($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Saison ajoutée',
            'season' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Season $object, string $data)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'Saison supprimée',
        );
    }

    /**
     * Returns the list of all persons in the array format
     * @return array
     */
    public function findAllByStatus($status)
    {
        return $this->em
            ->getRepository('App:Season')
            ->findAllByStatus($status)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Season $object)
    {
        if (null === $object->getName()) {
            throw new UnprocessableEntityHttpException('Missing data for Season -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Season $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'season-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Persists in DB
        $this->em->flush();
        $this->em->refresh($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Saison modifiée',
            'season' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Season $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        return $objectArray;
    }
}

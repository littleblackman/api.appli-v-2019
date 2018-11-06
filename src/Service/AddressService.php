<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Address;
use App\Entity\Person;
use App\Entity\PersonAddressLink;
use App\Form\AppFormFactoryInterface;
use App\Service\AddressServiceInterface;

/**
 * AddressService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class AddressService implements AddressServiceInterface
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
    public function addLink(int $personId, Address $address)
    {
        $person = $this->em->getRepository('App:Person')->findOneById($personId);
        if ($person instanceof Person) {
            $personAddressLink = new PersonAddressLink();
            $personAddressLink
                ->setPerson($person)
                ->setAddress($address)
            ;
            $this->em->persist($personAddressLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(Address $address, string $data)
    {
        $data = json_decode($data, true);
        $form = $this->formFactory->create('address-create', $address);
        $form->submit($data);

        //Checks if entity has been filled
        $this->isEntityFilled($address);

        //Adds data
        $address
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($this->user->getId())
            ->setSuppressed(false)
        ;
        $this->em->persist($address);

        //Adds links from person/s to address
        $links = $data['links'];
        if (null !== $links && is_array($links) && !empty($links)) {
            $this->addLink((int) $links['personId'], $address);
        }

        //Persists in DB
        $this->em->flush();
        $this->em->refresh($address);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Adresse ajoutée',
            'address' => $this->filter($address->toArray()),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Address $address, string $data)
    {
        $data = json_decode($data, true);

        $address
            ->setSuppressed(true)
            ->setSuppressedAt(new \DateTime())
            ->setSuppressedBy($this->user->getId())
        ;
        $this->em->persist($address);

        //Removes links from person/s to address
        $links = $data['links'];
        if (null !== $links && is_array($links) && !empty($links)) {
            $this->removeLink((int) $links['personId'], $address);
        }

        //Persists in DB
        $this->em->flush();

        return array(
            'status' => true,
            'message' => 'Adresse supprimée',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $addressArray)
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
            unset($addressArray[$unsetData]);
        }

        return $addressArray;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllInArray()
    {
        return $this->em
            ->getRepository('App:Address')
            ->findAllInArray()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Address $address)
    {
        if (null === $address->getName() ||
            null === $address->getAddress() ||
            null === $address->getPostal() ||
            null === $address->getTown()) {
            throw new UnprocessableEntityHttpException('Missing data for Address -> ' . json_encode($address->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Address $address, string $data)
    {
        $data = json_decode($data, true);
        $form = $this->formFactory->create('address-modify', $address);
        $form->submit($data);

        //Checks if entity has been filled
        $this->isEntityFilled($address);

        //Adds data
        $address
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->user->getId())
        ;

        //Persists in DB
        $this->em->persist($address);
        $this->em->flush();
        $this->em->refresh($address);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Adresse modifiée',
            'address' => $this->filter($address->toArray()),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeLink(int $personId, Address $address)
    {
        $person = $this->em->getRepository('App:Person')->findOneById($personId);
        if ($person instanceof Person) {
            $personAddressLink = $this->em->getRepository('App:PersonAddressLink')->findOneBy(array('person' => $person, 'address' => $address));
            $this->em->remove($personAddressLink);
        }
    }
}

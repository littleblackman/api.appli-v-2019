<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use App\Form\AppFormFactoryInterface;
use App\Service\MainServiceInterface;

/**
 * MainService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class MainService implements MainServiceInterface
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
    public function create($object)
    {
        $object
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($this->user->getId())
            ->setSuppressed(false)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        $object
            ->setSuppressed(true)
            ->setSuppressedAt(new \DateTime())
            ->setSuppressedBy($this->user->getId())
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($object)
    {
        $object
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->user->getId())
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function persist($object)
    {
        $this->em->persist($object);
        $this->em->flush();
        $this->em->refresh($object);
    }

    /**
     * {@inheritdoc}
     */
    public function submit($object, $formName, $data)
    {
        $data = json_decode($data, true);
        $form = $this->formFactory->create($formName, $object);
        $form->submit($data);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($objectArray)
    {
        //Main dates
        $dates = array(
            'createdAt',
            'updatedAt',
            'suppressedAt',
        );
        foreach ($dates as $date) {
            if (null !== $objectArray[$date]) {
                $objectArray[$date] = $objectArray[$date]->format('Y-m-d H:i:s');
            }
        }

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

        if ($this->security->isGranted('ROLE_TRAINEE') || $this->security->isGranted('ROLE_COACH')) {
            $specificData = array_merge(
                $specificData,
                array(
                    'addresses',
                )
            );

        }

        //Deletes unwanted data
        foreach (array_merge($globalData, $specificData) as $unsetData) {
            unset($objectArray[$unsetData]);
        }

        return $objectArray;
    }
}

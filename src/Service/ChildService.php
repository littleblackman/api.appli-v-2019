<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Child;
use App\Entity\ChildPersonLink;
use App\Entity\Person;
use App\Form\AppFormFactoryInterface;
use App\Service\PersonServiceInterface;
use App\Service\ChildServiceInterface;

/**
 * ChildService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ChildService implements ChildServiceInterface
{
    private $em;
    private $formFactory;
    private $personService;
    private $security;
    private $user;

    public function __construct(
        EntityManagerInterface $em,
        AppFormFactoryInterface $formFactory,
        PersonServiceInterface $personService,
        Security $security,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->personService = $personService;
        $this->security = $security;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function addLink(int $personId, string $relation, Child $child)
    {
        $person = $this->em->getRepository('App:Person')->findOneById($personId);
        if ($person instanceof Person) {
            $childPersonLink = new ChildPersonLink();
            $childPersonLink
                ->setRelation(htmlspecialchars($relation))
                ->setChild($child)
                ->setPerson($person)
            ;
            $this->em->persist($childPersonLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(Child $child, string $data)
    {
        $data = json_decode($data, true);
        $form = $this->formFactory->create('child-create', $child);
        $form->submit($data);

        //Checks if entity has been filled
        $this->isEntityFilled($child);

        //Adds data
        $child
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($this->user->getId())
            ->setSuppressed(false)
        ;
        $this->em->persist($child);

        //Adds links from person/s to child
        $links = $data['links'];
        if (null !== $links && is_array($links) && !empty($links)) {
            foreach ($links as $link) {
                $this->addLink((int) $link['personId'], $link['relation'], $child);
            }
        }

        //Persists in DB
        $this->em->flush();
        $this->em->refresh($child);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Enfant ajouté',
            'child' => $this->filter($child->toArray()),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Child $child, string $data)
    {
        $data = json_decode($data, true);

        $child
            ->setSuppressed(true)
            ->setSuppressedAt(new \DateTime())
            ->setSuppressedBy($this->user->getId())
        ;
        $this->em->persist($child);

        //Removes links from person/s to child
        $links = $data['links'];
        if (null !== $links && is_array($links) && !empty($links)) {
            foreach ($links as $link) {
                $this->removeLink((int) $link['personId'], $child);
            }
        }

        //Persists in DB
        $this->em->flush();

        return array(
            'status' => true,
            'message' => 'Enfant supprimé',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $childArray)
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
            unset($childArray[$unsetData]);
        }

        //Filters persons
        if (isset($childArray['persons']) && is_array($childArray['persons'])) {
            $persons = array();
            foreach ($childArray['persons'] as $key => $value) {
                $persons[] = $this->personService->filter($value);
            }
            $childArray['persons'] = $persons;
        }

        //Filters siblings
        if (isset($childArray['siblings']) && is_array($childArray['siblings'])) {
            $siblings = array();
            foreach ($childArray['siblings'] as $key => $value) {
                $siblings[] = $this->filter($value);
            }
            $childArray['siblings'] = $siblings;
        }

        return $childArray;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllInArray()
    {
        return $this->em
            ->getRepository('App:Child')
            ->findAllInArray()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllInSearch(string $term)
    {
        return $this->em
            ->getRepository('App:Child')
            ->findAllInSearch($term)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Child $child)
    {
        if (null === $child->getFirstname() ||
            null === $child->getLastname()) {
            throw new UnprocessableEntityHttpException('Missing data for Child -> ' . json_encode($child->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Child $child, string $data)
    {
        $data = json_decode($data, true);
        $form = $this->formFactory->create('child-modify', $child);
        $form->submit($data);

        //Checks if entity has been filled
        $this->isEntityFilled($child);

        //Adds data
        $child
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->user->getId())
        ;
        $this->em->persist($child);

        //Gets existing links to person
        $existingLinks = array();
        $currentLinks = $child->getPersons()->toArray();
        if (null !== $currentLinks && is_array($currentLinks) && !empty($currentLinks)) {
            foreach ($currentLinks as $currentLink) {
                $existingLinks[$currentLink->getPerson()->getPersonId()] = $currentLink->getRelation();
            }
        }

        //Gets submitted links to person
        $submittedLinks = array();
        $links = $data['links'];
        if (null !== $links && is_array($links) && !empty($links)) {
            foreach ($links as $link) {
                $submittedLinks[(int) $link['personId']] = $link['relation'];
            }
        }

        //Adds links from person/s to child
        $linksToAdd = array_diff($submittedLinks, $existingLinks);
        if (!empty($linksToAdd)) {
            foreach ($linksToAdd as $personId => $relation) {
                $this->addLink($personId, $relation, $child);
            }
        }

        //Removes links from person/s to child
        $linksToRemove = array_diff($existingLinks, $submittedLinks);
        if (!empty($linksToRemove)) {
            foreach ($linksToRemove as $personId => $relation) {
                $this->removeLink($personId, $child);
            }
        }

        //Persists in DB
        $this->em->flush();
        $this->em->refresh($child);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Enfant modifié',
            'child' => $this->filter($child->toArray()),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeLink(int $personId, Child $child)
    {
        $person = $this->em->getRepository('App:Person')->findOneById($personId);
        if ($person instanceof Person) {
            $childPersonLink = $this->em->getRepository('App:ChildPersonLink')->findOneBy(array('child' => $child, 'person' => $person));
            $this->em->remove($childPersonLink);
        }
    }
}

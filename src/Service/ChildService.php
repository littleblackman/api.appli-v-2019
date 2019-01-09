<?php

namespace App\Service;

use App\Entity\Child;
use App\Entity\ChildChildLink;
use App\Entity\ChildPersonLink;
use App\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * ChildService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ChildService implements ChildServiceInterface
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
     * Adds link between Child and Person
     */
    public function addLink(int $personId, string $relation, Child $object)
    {
        $person = $this->em->getRepository('App:Person')->findOneById($personId);
        if ($person instanceof Person) {
            $childPersonLink = new ChildPersonLink();
            $childPersonLink
                ->setRelation(htmlspecialchars($relation))
                ->setChild($object)
                ->setPerson($person)
            ;
            $this->em->persist($childPersonLink);
        }
    }

    /**
     * Adds link between Child and Child
     */
    public function addSibling(int $childId, string $relation, Child $object)
    {
        $child = $this->em->getRepository('App:Child')->findOneById($childId);
        if ($child instanceof Child) {
            $childChildLink = new ChildChildLink();
            $childChildLink
                ->setRelation(htmlspecialchars($relation))
                ->setChild($object)
                ->setChild($child)
            ;
            $this->em->persist($childChildLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new Child();
        $data = $this->mainService->submit($object, 'child-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->create($object);
        $this->mainService->persist($object);

        //Adds links from person/s to child
        if (isset($data['links'])) {
            $links = $data['links'];

            if (is_array($links) && !empty($links)) {
                foreach ($links as $link) {
                    $this->addLink((int) $link['personId'], $link['relation'], $object);
                }

                //Persists in DB
                $this->em->flush();
                $this->em->refresh($object);
            }
        }

        //Returns data
        return array(
            'status' => true,
            'message' => 'Enfant ajouté',
            'child' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Child $object, string $data)
    {
        $data = json_decode($data, true);

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        //Removes links from person/s to child
        $links = $data['links'];
        if (null !== $links && is_array($links) && !empty($links)) {
            foreach ($links as $link) {
                $this->removeLink((int) $link['personId'], $object);
            }

            //Persists in DB
            $this->em->flush();
        }

        return array(
            'status' => true,
            'message' => 'Enfant supprimé',
        );
    }

    /**
     * Returns the list of all children in the array format
     */
    public function findAll()
    {
        return $this->em
            ->getRepository('App:Child')
            ->findAll()
        ;
    }

    /**
     * Searches the term in the Child collection
     * @return array
     */
    public function findAllSearch(string $term)
    {
        return $this->em
            ->getRepository('App:Child')
            ->findAllSearch($term)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Child $object)
    {
        if (null === $object->getFirstname() ||
            null === $object->getLastname()) {
            throw new UnprocessableEntityHttpException('Missing data for Child -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Child $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'child-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Modifies links
        if (isset($data['links'])) {
            $links = $data['links'];

            //Gets submitted links to person
            $submittedLinks = array();
            if (is_array($links) && !empty($links)) {
                foreach ($links as $link) {
                    $submittedLinks[(int) $link['personId']] = $link['relation'];
                }
            }

            //Gets existing links to person
            $existingLinks = array();
            $currentLinks = $object->getPersons()->toArray();
            if (null !== $currentLinks && is_array($currentLinks) && !empty($currentLinks)) {
                foreach ($currentLinks as $currentLink) {
                    $existingLinks[$currentLink->getPerson()->getPersonId()] = $currentLink->getRelation();
                }
            }

            //Adds links from person/s to child
            $linksToAdd = array_diff($submittedLinks, $existingLinks);
            if (!empty($linksToAdd)) {
                foreach ($linksToAdd as $personId => $relation) {
                    $this->addLink($personId, $relation, $object);
                }
            }

            //Removes links from person/s to child
            $linksToRemove = array_diff($existingLinks, $submittedLinks);
            if (!empty($linksToRemove)) {
                foreach ($linksToRemove as $personId => $relation) {
                    $this->removeLink($personId, $object);
                }
            }
        }

        //Adds siblings
        if (isset($data['siblings'])) {
            foreach ($data['siblings'] as $sibling) {
                $this->addSibling($sibling['sibling'], $sibling['relation'], $object);
            }
        }

        //Persists in DB
        $this->em->flush();
        $this->em->refresh($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Enfant modifié',
            'child' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeLink(int $personId, Child $object)
    {
        $person = $this->em->getRepository('App:Person')->findOneById($personId);
        if ($person instanceof Person) {
            $objectPersonLink = $this->em->getRepository('App:ChildPersonLink')->findOneBy(array('child' => $object, 'person' => $person));
            $this->em->remove($objectPersonLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Child $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related persons
        if (null !== $object->getPersons()) {
            $persons = array();
            foreach($object->getPersons() as $personLink) {
                if (!$personLink->getPerson()->getSuppressed()) {
                    $personArray = $this->personService->toArray($personLink->getPerson());
                    $personArray['relation'] = $personLink->getRelation();
                    $persons[] = $personArray;
                }
            }
            $objectArray['persons'] = $persons;
        }

        //Gets related siblings
        if (null !== $object->getSiblings()) {
            $siblings = array();
            foreach($object->getSiblings() as $siblingLink) {
                if (!$siblingLink->getSibling()->getSuppressed()) {
                    $siblingArray = $this->mainService->toArray($siblingLink->getSibling()->toArray());
                    $siblingArray['relation'] = $siblingLink->getRelation();
                    $siblings[] = $siblingArray;
                }
            }
            $objectArray['siblings'] = $siblings;
        }

        return $objectArray;
    }
}

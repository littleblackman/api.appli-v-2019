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
    public function addLinks(Child $object, array $data)
    {
        if (array_key_exists('links', $data)) {
            $this->removeLinks($object);
            if (is_array($data['links']) && !empty($data['links'])) {
                foreach ($data['links'] as $link) {
                    $person = $this->em->getRepository('App:Person')->findOneById($link['personId']);
                    if ($person instanceof Person && !$person->getSuppressed()) {
                        $childPersonLink = new ChildPersonLink();
                        $childPersonLink
                            ->setRelation(htmlspecialchars($link['relation']))
                            ->setChild($object)
                            ->setPerson($person)
                        ;
                        $this->em->persist($childPersonLink);
                    }
                }
            }
        }
    }

    /**
     * Adds link between Child and Child
     */
    public function addSiblings(Child $object, array $data)
    {
        if (array_key_exists('siblings', $data)) {
            $this->removeSiblings($object);
            if (is_array($data['siblings']) && !empty($data['siblings'])) {
                foreach ($data['siblings'] as $sibling) {
                    $child = $this->em->getRepository('App:Child')->findOneById($sibling['siblingId']);
                    if ($child instanceof Child && !$child->getSuppressed()) {
                        $childChildLink = new ChildChildLink();
                        $childChildLink
                            ->setRelation(htmlspecialchars($sibling['relation']))
                            ->setChild($object)
                            ->setSibling($child)
                        ;
                        $this->em->persist($childChildLink);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new Child();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'child-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Adds links
        $this->addLinks($object, $data);
        $this->addSiblings($object, $data);

        //Persists data
        $this->mainService->persist($object);

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
    public function delete(Child $object)
    {
        //Removes links
        $this->removeLinks($object);
        $this->removeSiblings($object);

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

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

        //Adds links
        $this->addLinks($object, $data);
        $this->addSiblings($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Enfant modifié',
            'child' => $this->toArray($object),
        );
    }

    /**
     * Removes links from person/s to child
     */
    public function removeLinks(Child $object)
    {
        $links = $object->getPersons();
        if (null !== $links && !empty($links)) {
            foreach ($links as $link) {
                $this->em->remove($link);
            }
        }
    }

    /**
     * Removes links from child to child
     */
    public function removeSiblings(Child $object)
    {
        $siblings = $object->getSiblings();
        if (null !== $siblings && !empty($siblings)) {
            foreach ($siblings as $sibling) {
                $this->em->remove($sibling);
            }
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

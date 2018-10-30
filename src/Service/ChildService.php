<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Child;
use App\Entity\ChildPersonLink;
use App\Entity\Person;
use App\Service\PersonServiceInterface;
use App\Service\ChildServiceInterface;

class ChildService implements ChildServiceInterface
{
    private $em;
    private $personService;
    private $security;
    private $user;

    public function __construct(
        EntityManagerInterface $em,
        PersonServiceInterface $personService,
        Security $security,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->em = $em;
        $this->personService = $personService;
        $this->security = $security;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function create(Child $child, ParameterBag $parameters)
    {
        if (null !== $parameters->get('firstname') && null !== $parameters->get('lastname')) {
            $create = $this->hydrate($child, $parameters);

            $child
                ->setCreatedAt(new \DateTime())
                ->setCreatedBy($this->user->getId())
                ->setSuppressed(false)
            ;
            $this->em->persist($child);

            //Adds links from person/s to child
            $links = $parameters->get('links');
            if (null !== $links && is_array($links) && !empty($links)) {
                foreach ($links as $personId => $relation) {
                    $person = $this->em->getRepository('App:Person')->findOneById((int) $personId);
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
            }

            //Persists in DB
            $this->em->flush();

            $message = 'Enfant ajouté';

            //Returns data
            return array(
                'status' => $create,
                'message' => $message,
                'child' => $this->filter($child->toArray()),
            );
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Child $child, ParameterBag $parameters)
    {
        $child
            ->setSuppressed(true)
            ->setSuppressedAt(new \DateTime())
            ->setSuppressedBy($this->user->getId())
        ;
        $this->em->persist($child);
        //Removes links from person/s to address
        if (null !== $parameters->get('links')) {
            $links = $parameters->get('links');
            if (is_array($links) && !empty($links)) {
                foreach ($links as $personId) {
                    $person = $this->em->getRepository('App:Person')->findOneById((int) $personId);
                    if ($person instanceof Person) {
                        $childPersonLink = $this->em->getRepository('App:ChildPersonLink')->findOneBy(array('child' => $child, 'person' => $person));
                        $this->em->remove($childPersonLink);
                    }
                }
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
    public function getAllInArray()
    {
        return $this->em
            ->getRepository('App:Child')
            ->findAllInArray()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(Child $child, ParameterBag $parameters)
    {
        foreach ($parameters as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($child, $method)) {
                $child->$method(htmlspecialchars($value));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Child $child, ParameterBag $parameters)
    {
        $modify = $this->hydrate($child, $parameters);

        $child
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->user->getId())
        ;

        //Persists in DB
        $this->em->persist($child);
        $this->em->flush();

        $message = 'Enfant modifié';

        //Returns data
        return array(
            'status' => $modify,
            'message' => $message,
            'child' => $this->filter($child->toArray()),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $term, int $size)
    {
        $children = $this->em
            ->getRepository('App:Child')
            ->search($term, $size)
        ;

        $searchData = array();
        foreach ($children as $child) {
            $searchData[] = $this->filter($child->toArray());
        }

        return $searchData;
    }
}

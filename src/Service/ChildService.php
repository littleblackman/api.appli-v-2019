<?php

namespace App\Service;

use App\Entity\Child;
use App\Entity\ChildChildLink;
use App\Entity\ChildPersonLink;
use App\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\NotificationService;

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

    private $notificationService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService,
        PersonServiceInterface $personService,
        NotificationService $notificationService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
        $this->personService = $personService;
        $this->notificationService = $notificationService;
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
     * Adds specific data that could not be added via generic method
     */
    public function addSpecificData(Child $object, array $data)
    {
        $this->addLinks($object, $data);
        $this->addSiblings($object, $data);

        //Converts to boolean
        if (array_key_exists('franceResident', $data)) {
            $object->setFranceResident((bool) $data['franceResident']);
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
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->persist($object);

      
        $this->notificationService->create([
            "target_role" => "ROLE_ADMIN",
            "name"        => "Enfant",
            "description" => "Création d'un nouvel enfant : ".$object->getFullname(),
            "url"         => "/child/display/id/".$object->getChildId()."/"
        ]);

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

    public function findFastSearch(string $term) {

        $datas = [
                    ['name' => 'lastname', 'start' => ''],
                    ['name' => 'lastname', 'start' => '%'],
                    ['name' => 'firstname', 'start' => ''],
                    ['name' => 'firstname', 'start' => '%'],
        ];

        $result = null;

        $existsChild = [];

        foreach($datas as $data) {
            $childs = $this->em->getRepository('App:Child')->findAllSearchResult($term, $data['name'], $data['start']);
            foreach($childs as $child) {
                $childId = $child->getChildId();
                if(!key_exists($childId, $existsChild)) {
                    $result[] = [   'id'       => $childId,
                                    'fullname' => strtoupper($child->getLastname()).' '.$child->getFirstname(),
                                    'photo'    => $child->getPhoto()
                                ];
                }
                $existsChild[$childId] = $childId;
            }
        }
     
        return $result;
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
        $this->addSpecificData($object, $data);

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

    public function removeAllLinks(Child $object) {

    }

    public function removePerson($child, $personId) {
        foreach($child->getPersons( ) as $link) {
            if($link->getPerson()->getPersonId() == $personId) {
                $this->em->remove($link);
            }
        }
        $this->em->persist($child);
        $this->em->flush();
        return ['message' => 'liaison supprimée'];
    }

    /**
     * Removes links from person/s to child
     */
    public function removeLinks(Child $object)
    {
        if (!$object->getPersons()->isEmpty()) {
            foreach ($object->getPersons() as $link) {
                $this->em->remove($link);
            }
        }
    }

    /**
     * Removes links from child to child
     */
    public function removeSiblings(Child $object)
    {
        if (!$object->getSiblings()->isEmpty()) {
            foreach ($object->getSiblings() as $sibling) {
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

        //Gets related school
        if (null !== $object->getSchool() && !$object->getSchool()->getSuppressed()) {
            $objectArray['school'] = $this->mainService->toArray($object->getSchool()->toArray());
        }

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

        // get latest sport done
        if($latestRegistration = $this->em->getRepository('App:Registration')->findLatest($object)) {
            if (null !== $latestRegistration->getSports()) {
                $sports = array();
                foreach ($latestRegistration->getSports() as $sport) {
                    if (!$sport->getSport()->getSuppressed()) {
                        $sports[] = $this->mainService->toArray($sport->getSport()->toArray());
                    }
                }
                $objectArray['sports'] = $sports;
            }
            $objectArray['latestRegistrationId'] = $latestRegistration->getRegistrationId();

        }


        return $objectArray;
    }

    /**
     * retrieve birthdays staff of the current week
     */
    public function retrieveCurrentBirthdates()
    {

        $date_ref = date('Y-m-d');
        $n = 3; // nb days before and total of days = n*6
        $maxAge = 14;
        $start = date('Y-m-d', strtotime($date_ref.", -".$n." day"));
        $datesArray = array();
        $childs = $this->em->getRepository('App:Child')->retrieveCurrentBirthdates($start, $n*2, $maxAge);


        if($childs) {
                    foreach($childs as $child) {
                        $datesArray[$child->getBirthdate()->format('m-d')][] = [
                                        'full_name' => $child->getFirstname().' '.$child->getLastname(),
                                        'birthdate' => $child->getBirthdate()->format('Y-m-d')
                                    ];
                        }
        } else {
            $datesArray = ['message' => "aucun enfant n'est née dans cette période"];
        }


        return $datesArray;
    }
}

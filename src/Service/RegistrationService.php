<?php

namespace App\Service;

use App\Entity\Registration;
use App\Entity\RegistrationSportLink;
use App\Entity\Sport;
use App\Entity\Product;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Service\CascadeService;


/**
 * RegistrationService class.
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class RegistrationService implements RegistrationServiceInterface
{
    private $em;

    private $mainService;

    private $productService;

    private $childPresenceService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService,
        ProductServiceInterface $productService,
        ChildPresenceService $childPresenceService,
        CascadeService $cascadeService,
        PersonService $personService
    ) {
        $this->em = $em;
        $this->mainService = $mainService;
        $this->productService = $productService;
        $this->ChildPresenceService = $childPresenceService;
        $this->cascadeService = $cascadeService;
        $this->personService = $personService;
    }

    /**
     * Adds specific data that could not be added via generic method.
     */
    public function addSpecificData(Registration $object, array $data)
    {
        //Adds registration datetime
        if (null === $object->getRegistration()) {
            $object->setRegistration(new DateTime());
        }

        //Adds preferences
        if (array_key_exists('preferences', $data)) {
            $object->setPreferences(serialize($data['preferences']));
        }

        //Adds sessions
        if (array_key_exists('sessions', $data)) {
            $object->setSessions(serialize($data['sessions']));
        }

        //Adds sports
        if (array_key_exists('sports', $data)) {
            //Removes old links
            $this->removeSportsLinks($object);

            //Adds new links
            foreach ($data['sports'] as $sport) {
                $this->addSportLink($sport['sportId'], $object);
            }
        }
    }

    /**
     * Adds link between Registration and Sport.
     */
    public function addSportLink(int $sportId, Registration $object)
    {
        $sport = $this->em->getRepository('App:Sport')->findOneById($sportId);
        if ($sport instanceof Sport && !$sport->getSuppressed()) {
            $registrationSportLink = new RegistrationSportLink();
            $registrationSportLink
                ->setRegistration($object)
                ->setSport($sport)
            ;
            $this->em->persist($registrationSportLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new Registration();

        $this->mainService->create($object);

        $data = $this->mainService->submit($object, 'registration-create', $data);

        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->persist($object);

        // create presence, activity and transport
        if($data['product']) {
            $product = $this->em->getRepository('App:Product')->find($data['product']);
        } else {
            $product = $object->getProduct();
        }

        // return $this->productService->toArray($product);

        $target['hasLunch'] = $product->getLunch();

        $object->setHasLunch($product->getLunch());

        // if date is not selectable or not
        if (!$product->getIsDateSelectable()) {
            // if not selectable
            $linkDates = $product->getDates();
            foreach ($linkDates as $linkDate) {
                $target['dates'][] = $linkDate->getDate();
            }
        } else {
            // if date is selectable
            foreach ($data['sessions'] as $mydate) {
                $target['dates'][] = new \DateTime($mydate['date']);
            }
        }

        // if location is selectable or not
        if (!$product->getIsLocationSelectable()) {
            // not selectable
            $target['location'] = $product->getLocations()[0]->getLocation();
        } else {
            // if location is selectable
            $target['location'] = null;
        }

        // if hours is selectable or not
        if (!$product->getIsHourSelectable()) {
            // not selectable
            $target['hours']['start'] = $product->getHours()[0]->getStart();
            $target['hours']['end'] = $product->getHours()[0]->getEnd();
        } else {
            // if location is selectable
            $target['hours'] = [];
        }

        // if sport is selectable or not
        if (!$product->getIsSportSelectable()) {
            // not selectable
            $linkSports = $product->getSports();
            foreach ($linkSports as $linkSport) {
                $target['sport'][] = $linkSports->getSport();
            }
        } else {
            // if sports is selectable
            foreach ($data['sports'] as $sportData) {
                $sport = $this->em->getRepository('App:Sport')->find($sportData['sportId']);
                $target['sports'][] = $sport;
            }
        }

        // if transport
        if ($product->getTransport()) {
            $target['pickup']['dropin'] = $product->getHourDropin();
            $target['pickup']['dropoff'] = $product->getHourDropoff();
        }

        $target['hasTransport'] = $product->getTransport();
        $object->setHasTransport($product->getTransport());

        $this->mainService->modify($object);
        $this->mainService->persist($object);

        // had data in registration

        // create presence and cascade to transport
        foreach ($target['dates'] as $targetDate) {
            $this->cascadeService->createFromRegistrationAndData($object, $targetDate, $target['location'], $target['hours'], $target['sports'], $target['hasLunch'], $target['hasTransport'], $target['pickup']);
         }


        //Returns data
        return array(
            'status' => true,
            'message' => 'Inscription ajoutée',
            'registration' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Registration $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        //Deletes links to sports
        $this->removeSportsLinks($object);

        return array(
            'status' => true,
            'message' => 'Inscription supprimée',
        );
    }

    /**
     * Returns the list of all registrations related to status in the array format.
     */
    public function findAllByStatus($status)
    {
        return $this->em
            ->getRepository('App:Registration')
            ->findAllByStatus($status)
        ;
    }

    /**
     * Returns the list of all registrations related to person and status in the array format.
     */
    public function findAllByPersonAndStatus($personId, $status)
    {
        return $this->em
            ->getRepository('App:Registration')
            ->findAllByPersonAndStatus($personId, $status)
        ;
    }

    /**
     * Returns the list of regisration by child from date to date
     */
    public function findAllByChild($childId, $from, $to) {

        $child = $this->em->getRepository('App:Child')->find($childId);
        $from = new DateTime($from);
        $to   = new DateTime($to);
        if($registrations = $this->em->getRepository('App:Registration')->findAllByChild($child, $from, $to)) {
            foreach($registrations as $registration) {
                $result[] = $this->toArray($registration);
            }
        } else {
            $result = null;
        }
        
       
        return $result;

    }

    /**
     * Returns the list of all registrations related to person without the cart status in the array format.
     */
    public function findAllWithoutCart()
    {
        return $this->em
            ->getRepository('App:Registration')
            ->findAllWithoutCart()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Registration $object)
    {
        if (null === $object->getChild() ||
            null === $object->getProduct()) {
            throw new UnprocessableEntityHttpException('Missing data for Registration -> '.json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Registration $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'registration-modify', $data);
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Inscription modifiée',
            'registration' => $this->toArray($object),
        );
    }

    /**
     * Removes links from Registration.
     */
    public function removeSportsLinks(Registration $object)
    {
        //Removes links to sports
        if (!$object->getSports()->isEmpty()) {
            foreach ($object->getSports() as $link) {
                $this->em->remove($link);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Registration $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related child
        if (null !== $object->getChild() && !$object->getChild()->getSuppressed()) {
            $objectArray['child'] = $this->mainService->toArray($object->getChild()->toArray());
        }

        //Gets related person
        if (null !== $object->getPerson() && !$object->getPerson()->getSuppressed()) {
            $objectArray['person'] = $this->mainService->toArray($object->getPerson()->toArray());
        } else {
            if($person = $this->personService->findByUserId($object->getCreatedBy())) {
                $objectArray['person'] = $this->mainService->toArray($person->toArray());
            }
        }

        //Gets related product
        if (null !== $object->getProduct() && !$object->getProduct()->getSuppressed()) {
            $objectArray['product'] = $this->productService->toArray($object->getProduct());
        } 

        //Gets related location
        if (null !== $object->getLocation() && !$object->getLocation()->getSuppressed()) {
            $objectArray['location'] = $this->mainService->toArray($object->getLocation()->toArray());
        }

        //Gets related sports
        if (null !== $object->getSports()) {
            $sports = array();
            foreach ($object->getSports() as $sport) {
                if (!$sport->getSport()->getSuppressed()) {
                    $sports[] = $this->mainService->toArray($sport->getSport()->toArray());
                }
            }
            $objectArray['sports'] = $sports;
        }

        //Gets related transaction
        if (null !== $object->getTransaction() && !$object->getTransaction()->getSuppressed()) {
            $objectArray['transaction'] = $this->mainService->toArray($object->getTransaction()->toArray());
        }

        return $objectArray;
    }
}

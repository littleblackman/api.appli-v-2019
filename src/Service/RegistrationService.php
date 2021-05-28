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
        PersonService $personService,
        PickupService $pickupService
    ) {
        $this->em = $em;
        $this->mainService = $mainService;
        $this->productService = $productService;
        $this->ChildPresenceService = $childPresenceService;
        $this->cascadeService = $cascadeService;
        $this->personService = $personService;
        $this->pickupService = $pickupService;
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

        $dataArray2 = is_array($data) ? $data : json_decode($data, true);

        $data = $dataArray2;

        if(isset($data['freeAddress'])) unset($data['freeAddress']);
        if(isset($data['freePostal'])) unset($data['freePostal']);
        if(isset($data['freeTown'])) unset($data['freeTown']);
        if(isset($data['pickupDatePaiement'])) unset($data['pickupDatePaiement']);

        //Submits data
        $object = new Registration();

        $this->mainService->create($object);

        $data = $this->mainService->submit($object, 'registration-create', $data);

        $this->addSpecificData($object, $data);


        if($object->getPreferences() == null) {
            $dataArray = is_array($data) ? $data : json_decode($data, true);
            if(isset($dataArray['address'])) {

                if($addressPref = $this->em->getRepository('App:Address')->find($dataArray['address'])) {
                    $arr[0]['address'] = $dataArray['address'];
                    $arr[0]['postal']  = $addressPref->getPostal();

                    $object->setPreferences(serialize($arr));
                }               
            } 

        }

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->persist($object);
       
        if($object->getStatus() != "cart") {
            $message = $this->cascadeService->cascadeFromRegistration($object);
        } else {
            $message = "registration ".$object->getRegistrationId()." created in cart status";
        }


        /*** UPDATE FREE ADDRESS ON PICKUP */

        // update free address if exist
        if(isset($dataArray2['freeAddress'])) {
            if($dataArray2['freeAddress'] != "" && $dataArray2['freePostal'] != ""  &&   $dataArray2['freeTown']) {
                $pickups = $this->em->getRepository('App:Pickup')->findBy(['registration' => $object]);
                if (!empty($pickups)) {
                    foreach ($pickups as $pickup) {
                        $pickup->setAddress($dataArray2['freeAddress'].' - '.$dataArray2['freePostal'].' - '.$dataArray2['freeTown']);
                        $pickup->setPostal($dataArray2['freePostal']);
                        $this->pickupService->checkCoordinates($pickup);
                        $this->mainService->persist($pickup);
                    }
                }
            }
    
        }
       

        /*** UPDATE PAIEMENT  */

        if(isset($dataArray2['pickupDatePaiement']))  {
            if($dataArray2['pickupDatePaiement'] != "") {

                $el = explode(',' , $dataArray2['pickupDatePaiement']);

                if(!isset($el[1])) {
                    $arr = [$dataArray2['pickupDatePaiement']];
                } else {
                    $arr = $el;
                }

                foreach($arr as $a) {
                    $elements = explode('|', $a);

                    $price = $elements[0];
                    $date  = $elements[1];
        
                    $pickups = $this->em->getRepository('App:Pickup')->findByRegistrationAndDate($date, $object->getRegistrationId());
                    foreach ($pickups as $pickup) {
                        if($pickup->getKind() == "dropin") {
                            $pickup->setPaymentDue($price);
                            $this->mainService->persist($pickup);
                        }
                    }
                }
            }
        }


        //Returns data
        return array(
            'status' => true,
            'message' => 'Inscription ajoutée',
            'messages' => $message,
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


    public function awaitingPayment($from = null, $to = null) {

        if(!$registrations = $this->em->getRepository('App:Registration')->findAwaiting()) return ['message' => 'no regisrations founded'];
        foreach($registrations as $registration) {
            $result[] = $this->toArray($registration);
        }
        return $result;
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

        $firstStatus = $object->getStatus();

        //Submits data
        $data = $this->mainService->submit($object, 'registration-modify', $data);
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);
          
        if($firstStatus == "cart" && $object->getStatus() == "payed") {

            $arr[] = 'in';
            $message = $this->cascadeService->cascadeFromRegistration($object);
        } else {
            $arr[] = 'out';

        }

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

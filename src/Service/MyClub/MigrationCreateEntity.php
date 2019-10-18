<?php
namespace App\Service\MyClub;

use App\Service\MyClub\MigrationQueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\MainServiceInterface;
use App\Service\MyClub\MigrationTransformDatas;
use App\Service\PickupService;
use App\Service\PickupActivityService;



use App\Entity\User;
use App\Entity\Person;
use App\Entity\UserPersonLink;
use App\Entity\Address;
use App\Entity\PersonAddressLink;
use App\Entity\Phone;
use App\Entity\PersonPhoneLink;
use App\Entity\Child;
use App\Entity\ChildPersonLink;
use App\Entity\ChildChildLink;



use App\Entity\Pickup;
use App\Entity\PickupActivity;
use App\Entity\Location;



use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Id\AssignedGenerator;

use c975L\UserBundle\Service\UserServiceInterface;

use DateTime;


/**
 * Class MigrationCreateEntity
 *
 * Create the new entity in appli-v from myclub array
 */
class MigrationCreateEntity
{

  use MigrationTransformDatas;

  private $em;
  private $userService;
  private $mainService;
  private $pickupService;

  public function __construct(EntityManagerInterface $em,
                          UserServiceInterface $userService,
                          MainServiceInterface $mainService,
                          PickupService $pickupService)
  {
      $this->em = $em;
      $this->userService = $userService;
      $this->mainService = $mainService;
      $this->pickupService = $pickupService;
  }

  public function createUser($userData)
  {

    if (!$user = $this->userService->findUserByEmail($userData['email'])) {

        $user = new User();
        $user->setEmail($userData['email']);
        $user->setRoles(['ROLE_PARENT']);
        $plainPassword = $userData['password'];

        $this->userService->add($user);
        $user
            ->setAllowUse(true)
            ->setEnabled(true)
            ->setToken(null)
        ;

        $this->em->persist($user);
        $this->em->flush();
      }

      return $user;
  }

  public function createPerson($personData, $user = null) {

      if($user->getUserPersonLink()) return $user->getUserPersonLink()->getPerson();

      $person = new Person();

      $person->setCreatedAt(new DateTime());
      $person->setCreatedBy(99);
      $person->setUpdatedAt(new DateTime());
      $person->setUpdatedBy(99);
      $person->setSuppressed(false);

      $data = $this->mainService->submit($person, 'person-create', $personData);

      //Adds links from user to person
      if (null !== $user) {
          $userPersonLink = new UserPersonLink();
          $userPersonLink
              ->setUser($user)
              ->setPerson($person)
          ;
          $this->em->persist($userPersonLink);
      }
      //Persists data
      $this->mainService->persist($person);

      //Adds relations that must be added after the creation of the Person
      $this->em->refresh($person);
      $this->em->flush();


      return $person;
  }

  public function createAddressLink($person, $dataAddress) {

    if(!$person->getAddresses()) return $person;

    $object = new Address();

    $object->setCreatedAt(new DateTime());
    $object->setCreatedBy(99);
    $object->setUpdatedAt(new DateTime());
    $object->setUpdatedBy(99);
    $object->setSuppressed(false);

    $data = $this->mainService->submit($object, 'address-create', $dataAddress);

    //Checks coordinates
    $this->mainService->addCoordinates($object);

    $this->em->persist($object);

    // add personn link
    $personAddressLink = new PersonAddressLink();
    $personAddressLink
        ->setPerson($person)
        ->setAddress($object)
    ;
    $this->em->persist($personAddressLink);

    $this->em->flush();

    return $person;

  }

  public function createPhoneLink($person, $dataPhone) {

    if(!$person->getPhones()) return $person;

    //Submits data
    $object = new Phone();

    $object->setCreatedAt(new DateTime());
    $object->setCreatedBy(99);
    $object->setUpdatedAt(new DateTime());
    $object->setUpdatedBy(99);
    $object->setSuppressed(false);

    $data = $this->mainService->submit($object, 'phone-create', $dataPhone);

    $this->em->persist($object);

    $personPhoneLink = new PersonPhoneLink();
    $personPhoneLink
        ->setPerson($person)
        ->setPhone($object)
    ;

    $this->em->persist($personPhoneLink);

    $this->em->flush();

    return $person;

  }

  public function createChildAndLink($person, $dataChild) {

     $isNew = 0;
     if(!$object = $this->em->getRepository('App:Child')->find($dataChild['child_id'])) {
       $object = new Child();
       $object->setChildId($dataChild['child_id']);
       $isNew = 1;
     }


      $object->setCreatedAt(new DateTime($dataChild['c_created_at']));
      $object->setCreatedBy(99);
      $object->setUpdatedAt(new DateTime());
      $object->setUpdatedBy(99);
      $object->setSuppressed(false);

      unset($dataChild['family_id']);
      unset($dataChild['child_id']);
      unset($dataChild['c_created_at']);

      $data = $this->mainService->submit($object, 'child-create', $dataChild);

      $this->em->persist($object);

      if($isNew == 1) {
        $metadata = $this->em->getClassMetaData(get_class($object));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
      }

      //Persists data
      $this->em->flush();

      $childPersonLink = new ChildPersonLink();
      $childPersonLink
          ->setRelation(htmlspecialchars('Parent'))
          ->setChild($object)
          ->setPerson($person)
      ;
      $this->em->persist($childPersonLink);

      $this->em->flush();

      return $object;

  }

  public function createSiblings($child_ref, $childs)
  {
    foreach($childs as $c) {
      if($child_ref != $c) {
        $childChildLink = new ChildChildLink();
        $childChildLink
            ->setRelation('fratrie')
            ->setChild($child_ref)
            ->setSibling($c)
        ;
        $this->em->persist($childChildLink);
        $this->em->flush();

        if(($child_ref->getGender() == 'h' || $child_ref->getGender() == 'f') && $c->getGender() == 'h') $link = "Frère";
        if(($child_ref->getGender() == 'h' || $child_ref->getGender() == 'f') && $c->getGender() == 'f') $link = "Soeur";

        $messages[] = "lien entre ".$child_ref->getFirstname().' '.$child_ref->getLastname().
                   ' et '.$c->getFirstname().' '.$c->getLastname(). ' : '.$link;
      }
    }
    return $messages;
  }


  public function createPickupActivity($child, $activityData, $presencesIdList)
  {

    $location = $this->em->getRepository('App:Location')->find(6);
    if(!$sport = $this->em->getRepository('App:Sport')->find($activityData['sport_id'])) return null;
    $dateActivity = new DateTime($activityData['date_seance']);

    $start_time = '00:00:00';
    $end_time = '00:00:00';

    if(key_exists($child->getChildId(), $presencesIdList)) $activityData['moment'] = $presencesIdList[$child->getChildId()];


    if($activityData['moment'] == 'AM') {
      $start_time = "08:00:00";
      $end_time   = "11:30:00";
    }

    if($activityData['moment'] == 'PM') {
      $start_time = "14:00:00";
      $end_time   = "16:00:00";
    }

    if($activityData['moment'] == 'DAY') {
      $start_time = "08:00:00";
      $end_time   = "16:00:00";
    }

    $starTimeActivity = new DateTime('1970-01-01 '.$start_time);
    $endTimeActivity = new DateTime('1970-01-01 '.$end_time);


    if(
        $this->em->getRepository('App:PickupActivity')->findBy([
                                                                'location' => $location,
                                                                'date' => $dateActivity,
                                                                'child' => $child,
                                                                'sport' => $sport,
                                                                'start' => $starTimeActivity,
                                                                'end' => $endTimeActivity
                                                                ])
      ) {
        return null;
      }


    $object = new PickupActivity();
    $object->setCreatedAt(new DateTime());
    $object->setCreatedBy(99);
    $object->setUpdatedAt(new DateTime());
    $object->setUpdatedBy(99);
    $object->setSuppressed(false);
    $object->setChild($child);
    $object->setSport($sport);

    $object->setDate($dateActivity);
    $object->setLocation($location);



    $object->setStart($starTimeActivity);
    $object->setEnd($endTimeActivity);

    $this->em->persist($object);
    $this->em->flush();

    return $object;
  }


  public function createPickup($child, $transportData)
  {

    //Submits data
    $object = new Pickup();
    $object->setCreatedAt(new DateTime());
    $object->setCreatedBy(99);
    $object->setUpdatedAt(new DateTime());
    $object->setUpdatedBy(99);
    $object->setSuppressed(false);

    ($transportData['type'] == 'A') ? $kind = "dropin" : $kind = "dropoff";

    if($kind == "dropin" && $transportData['moment'] == 'AM') {
      ($transportData['time_rdv']) ? $start_time = $transportData['time_rdv'] : $start_time = "08:00:00";
      $start = $transportData['date'].' '.$start_time;
    }

    if($kind == "dropoff" && $transportData['moment'] == 'AM') {
      ($transportData['time_rdv']) ? $start_time = $transportData['time_rdv'] : $start_time = "11:30:00";
      $start = $transportData['date'].' '.$start_time;
    }

    if($kind == "dropin" && $transportData['moment'] == 'PM') {
      ($transportData['time_rdv']) ? $start_time = $transportData['time_rdv'] : $start_time = "13:00:00";
      $start = $transportData['date'].' '.$start_time;
    }

    if($kind == "dropoff" && $transportData['moment'] == 'PM') {
      ($transportData['time_rdv']) ? $start_time = $transportData['time_rdv'] : $start_time = "16:30:00";
      $start = $transportData['date'].' '.$start_time;
    }

    $postal = $this->extractPostalCode($transportData['address']);

    $object->setChild($child);
    $object->setKind($kind);
    $object->setStart(new DateTime($start));
    $object->setPhone(null);
    $object->setPostal($postal);
    $object->setAddress($transportData['address']);

    //Checks coordinates
    $this->pickupService->checkCoordinates($object);

    $this->em->persist($object);
    $this->em->flush();

    return $object;

  }
}

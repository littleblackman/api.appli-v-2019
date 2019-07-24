<?php

namespace App\Service\MyClub;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Service\MyClub\MigrationImportFunctions;
use App\Service\MyClub\MigrationTransformDatas;
use App\Service\MyClub\MigrationCreateEntity;

use App\Service\PickupService;


use DateTime;


/**
 * MigrationMyClubService class
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class MigrationMyClubService
{

    use MigrationTransformDatas;
    use MigrationImportFunctions;

    private $em;
    private $createEntity;

    public function __construct(EntityManagerInterface $em, MigrationCreateEntity $createEntity, PickupService $pickupService)
    {
        $this->em = $em;
        $this->createEntity = $createEntity;
        $this->pickupService = $pickupService;
    }

  
    public function getTransportByDate($date, $limit = 30)
    {

        // find transport by dates
        if(!$transports = $this->importTransport($date, $limit)) $messages['info'] = "Pas de transport à créer pour le ".date('d/m/Y', strtotime($date));

        // parse transport by transport
        foreach($transports as $dataTransport) {

            // extract child
            $childArray = $this->extractChild($dataTransport);

            if($this->isValidChild($childArray)) {

                        // check if child exist
                        $child = $this->em->getRepository('App:Child')->findOneBy([
                                                                          'lastname' => $childArray['lastname'],
                                                                          'firstname' => $childArray['firstname'],
                                                                          'birthdate' => new DateTime($childArray['birthdate'])
                                                              ]);
                        // if child doesn't exist create family and child
                        if(!$child)
                        {
                                    // create family from child data
                                    $familyArray = $this->importFamily($childArray['family_id']);

                                    $datas = $this->extractFamilyDatas($familyArray);

                                    // update user & person
                                    $user = $this->createEntity->createUser($datas['user']);
                                    $person = $this->createEntity->createPerson($datas['person'], $user);

                                    // add and create address
                                    foreach($datas['address'] as $dataAddress) {
                                        $person = $this->createEntity->createAddressLink($person, $dataAddress);
                                    }

                                    // add phones
                                    foreach($datas['phones'] as $dataPhone) {
                                        $person = $this->createEntity->createPhoneLink($person, $dataPhone);
                                    }

                                    // create child
                                    foreach($datas['childs'] as $dataChild) {
                                        $childs[] = $this->createEntity->createChildAndLink($person, $dataChild);
                                    }

                                    // retrieve child when created
                                    $child = $this->em->getRepository('App:Child')->find($childArray['child_id']);

                                    // create sibling
                                    if(count($childs) > 1) {
                                            $messages['sibling'] = $this->createEntity->createSiblings($child, $childs);
                                    }
                                    unset($childs);
                        }

                        // create pickup
                        if($pickup = $this->createEntity->createPickup($child, $dataTransport)) {
                            $this->setImported('ea_transport', $dataTransport['transport_id']);
                            $messages['imported'][$dataTransport['transport_id']] = [$this->pickupService->toArray($pickup)];
                        } else {
                            $messages['info'] = "pas de pickup créé pour transport_id ".date('d/m/Y', strtotime($dataTransport['transport_id']));
                        }

            } else {
                        (isset($childArray['firstname']) && isset($childArray['lastname'])) ? $childname = $childArray['firstname'].' '.$childArray['lastname'] : $childname = "unknown";
                        $message = "Le childArray n'est pas valide pour ".$childname;
                        $messages['errors'][] = ['message' => $message, 'childArray' => $childArray];
            }

        }

        return $messages;
    }

    public function createFamily($family_id)
    {
            $family = $this->importFamily($family_id);
    }


}

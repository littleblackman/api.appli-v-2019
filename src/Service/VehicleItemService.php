<?php

namespace App\Service;

use App\Entity\Vehicle;
use App\Entity\VehicleItem;
use App\Entity\VehicleCheckup;
use App\Entity\VehicleCheckupItem;

use App\Entity\Staff;
use DateTime;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * VehicleItemService class
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class VehicleItemService implements VehicleItemServiceInterface
{
    private $em;

    private $mainService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
    }

    public function list()
    {
        $items = $this->em->getRepository('App:VehicleItem')->findBy(['suppressed' => 0], ['name' => 'asc']);
        foreach($items as $item)
        {
            $result[] = $item->toArray();
        }
        return $result;
    }


    public function validCheckup(string $data)
    {
        $data = json_decode($data, true);
        $staff = $this->em->getRepository('App:Staff')->find($data['staff_id']);
        $vehicle = $this->em->getRepository('App:Vehicle')->find($data['vehicle_id']);
        if($staff == null || $vehicle == null) {
        return [
                            "message" => "Staff et/ou véhicule non trouvé"
                    ];
        }

        (isset($data['photo'])) ? $link = $data['photo'] : $link = null;

        // create checkup or update checkup if the same day
        if(!$checkup = $this->em->getRepository('App:VehicleCheckup')->findByDateStaffVehicle($data['date_checkup'], $staff, $vehicle)) {
            $checkup = new VehicleCheckup();
        } else {
            foreach($checkup->getItems() as $linkItem) {
                $checkup->removeItem($linkItem);
                $this->em->flush();
            }

        }

        $checkup->setStaff($staff);
        $checkup->setVehicle($vehicle);
        $checkup->setDateCheckup($data['date_checkup']);
        $checkup->setComment($data['comment']);
        $checkup->setIsOk($data['is_ok']);
        $checkup->setPhoto($link);

        $this->mainService->create($checkup);
        $this->mainService->persist($checkup);


        foreach($data['items'] as $constant_key => $value)
        {
            if($item = $this->em->getRepository('App:VehicleItem')->findOneBy(['constantKey' => $constant_key])) {
                $object = new VehicleCheckupItem();
                $object->setCheckup($checkup);
                $object->setItem($item);
                $object->setIsOk($value);
                $this->em->persist($object);
            }
        }
        $this->em->flush();

        //Returns data
    return array(
        'status' => true,
        'message' => 'checkup créé',
        'checkup' => $checkup->toArray("light"),
            );

    }

    public function checkupVehicleList($vehicle_id)
    {
        $vehicle = $this->em->getRepository('App:Vehicle')->find($vehicle_id);
        $checkups = $this->em->getRepository('App:VehicleCheckup')->findBy(['vehicle' => $vehicle], ['dateCheckup' => 'desc']);
        $results = [];
        foreach($checkups as $checkup) {
            $results[] = $checkup->toArray("light");
        }
        return array(
            'status' => true,
            'checkups' => $results
                );


    }




}

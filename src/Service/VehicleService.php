<?php

namespace App\Service;

use App\Entity\Vehicle;
use App\Entity\VehicleFuel;
use App\Entity\VehicleWashing;
use App\Entity\VehicleAction;


use App\Entity\Staff;
use DateTime;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * VehicleService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class VehicleService implements VehicleServiceInterface
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

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new Vehicle();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'vehicle-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Véhicule ajouté',
            'vehicle' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Vehicle $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'Véhicule supprimé',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findAllInArray()
    {
        return $this->em
            ->getRepository('App:Vehicle')
            ->findAllInArray()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Vehicle $object)
    {
        if (null === $object->getName() ||
            null === $object->getMatriculation()) {
            throw new UnprocessableEntityHttpException('Missing data for Vehicle -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Vehicle $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'vehicle-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Véhicule modifié',
            'vehicle' => $this->toArray($object),
        );
    }

    public function listVehicleBetweenDate($type, $from, $to, $limit = null, $vehicle_id = null) {

        $vehicles = $this->em->getRepository('App:Vehicle')->findBy(['suppressed' => 0]);

        foreach($vehicles as $vehicle) {
            $tests[] = $vehicle->toArray();

            if(!$actions = $this->listActionBetweenDate($from, $to, $vehicle->getVehicleId(), $limit, true)) $actions = [];
            if(!$fuels = $this->listFuelBetweenDate($from, $to, $vehicle->getVehicleId(), $limit, true)) $fuels = [];
            if(!$washings = $this->listWashingBetweenDate($from, $to, $vehicle->getVehicleId(), $limit, true)) $washings = [];

            foreach(['actions' => $actions, 'fuels' => $fuels, 'washings' => $washings] as $name => $objects) {
                $objectsArray = []; 
                foreach($objects as $object) {
                    $objectsArray[] = $object->toArray('exclude-vehicle');
                }
                $elements[$name] = $objectsArray;
            }
            $elements['vehicle']  = $vehicle->toArray('light');
            $result[$vehicle->getVehicleId()] = $elements;         
        }
        return $result;
    }


    /***** wadhing methods **************************************/

    public function addWashing(string $data)
    {
        $data = json_decode($data, true);
        $staff = $this->em->getRepository('App:Staff')->find($data['staff_id']);
        $vehicle = $this->em->getRepository('App:Vehicle')->find($data['vehicle_id']);

        if($staff == null || $vehicle == null) {
            return [
                            "message" => "Staff non trouvé et/ou véhicule non trouvé"
                    ];
        }

        (!isset($data['description'])) ? $description = null : $description = $data['description'];

        $object = new VehicleWashing();
        $object->setVehicle($vehicle);
        $object->setStaff($staff);
        $object->setDescription($description);
        $object->setAmount($data['amount']);
        $object->setMileage($data['mileage']);
        $object->setDateAction($data['date_action']);

        $this->mainService->create($object);
        $this->mainService->persist($object);

        $vehicle->setMileage($object->getMileage());

        $this->mainService->create($vehicle);
        $this->mainService->persist($vehicle);

        //Returns data
        return array(
            'status' => true,
            'message' => 'données ajoutées',
            'action' => $object->toArray(),
        );
    }

    public function listWashingByDate($date = null, $vehicle_id = null)
    {
        if($vehicle_id) {
            $vehicle = $this->em->getRepository('App:Vehicle')->find($vehicle_id);
            $actions = $this->em->getRepository('App:VehicleWashing')->findBy(['dateAction' => new DateTime($date), 'vehicle' => $vehicle], ['id' => 'desc']);
        } else {
          $washings = $this->em->getRepository('App:VehicleWashing')->findBy(['dateAction' => new DateTime($date)], ['id' => 'desc']);
        }
        $result = [];
        foreach($washings as $washing)
        {
            $result[] = $action->toArray("light");
        }
        if(!$result) $result = ['message' => 'aucune donnée trouvée ce jour'];
        return $result;
    }


    public function listWashingByVehicle($vehicle_id = null, $limit = 100)
    {
        $vehicle = $this->em->getRepository('App:Vehicle')->find($vehicle_id);
        $washings = $this->em->getRepository('App:VehicleWashing')->findBy(['vehicle' => $vehicle], ['id' => 'desc'], $limit);

        $result = [];
        foreach($washings as $washing)
        {
            $result[] = $action->toArray("light");
        }
        if(!$result) $result = ['message' => 'aucune donnée trouvée ce jour'];
        return $result;
    }


    public function listWashingBetweenDate($from, $to, $vehicle_id = null, $limit = null, $returnObject = false)
    {

        $fromDate = new DateTime($from);
        $toDate = new DateTime($to);

        if($vehicle_id) {
            $vehicle = $this->em->getRepository('App:Vehicle')->find($vehicle_id);
        } else {
            $vehicle = null;
        }

        $washings = $this->em->getRepository('App:VehicleWashing')->findBetweenDate($fromDate, $toDate, $vehicle, $limit);

        if($returnObject) return $washings;

        $result = [];
        foreach($washings as $washing)
        {
            $result[] = $washing->toArray("light");
        }
        if(!$result) $result = ['message' => 'aucune donnée trouvée ce jour'];
        return $result;
    }



    /***** fuel methods **************************************/

    public function addFuel(string $data)
    {
        $data = json_decode($data, true);
        $staff = $this->em->getRepository('App:Staff')->find($data['staff_id']);
        $vehicle = $this->em->getRepository('App:Vehicle')->find($data['vehicle_id']);

        if($staff == null || $vehicle == null) {
            return [
                            "message" => "Staff non trouvé et/ou véhicule non trouvé"
                    ];
        }

        $object = new VehicleFuel();
        $object->setVehicle($vehicle);
        $object->setStaff($staff);
        $object->setQuantity($data['quantity']);
        $object->setAmount($data['amount']);
        $object->setMileage($data['mileage']);
        $object->setDateAction($data['date_action']);

        $this->mainService->create($object);
        $this->mainService->persist($object);

        $vehicle->setMileage($object->getMileage());

        $this->mainService->create($vehicle);
        $this->mainService->persist($vehicle);

        //Returns data
        return array(
            'status' => true,
            'message' => 'données ajoutées',
            'action' => $object->toArray(),
        );
    }

    public function listFuelByDate($date = null, $vehicle_id = null)
    {
        if($vehicle_id) {
            $vehicle = $this->em->getRepository('App:Vehicle')->find($vehicle_id);
            $actions = $this->em->getRepository('App:VehicleFuel')->findBy(['dateAction' => new DateTime($date), 'vehicle' => $vehicle], ['id' => 'desc']);
        } else {
          $actions = $this->em->getRepository('App:VehicleFuel')->findBy(['dateAction' => new DateTime($date)], ['id' => 'desc']);
        }
        $result = [];
        foreach($actions as $action)
        {
            $result[] = $action->toArray("light");
        }
        if(!$result) $result = ['message' => 'aucune donnée trouvée ce jour'];
        return $result;
    }


    public function listFuelByVehicle($vehicle_id = null, $limit = 100)
    {
        $vehicle = $this->em->getRepository('App:Vehicle')->find($vehicle_id);
        $actions = $this->em->getRepository('App:VehicleFuel')->findBy(['vehicle' => $vehicle], ['id' => 'desc'], $limit);

        $result = [];
        foreach($actions as $action)
        {
            $result[] = $action->toArray("light");
        }
        if(!$result) $result = ['message' => 'aucune donnée trouvée ce jour'];
        return $result;
    }


    public function listFuelBetweenDate($from, $to, $vehicle_id = null, $limit = null, $returnObject = false)
    {

        $fromDate = new DateTime($from);
        $toDate = new DateTime($to);

        if($vehicle_id) {
            $vehicle = $this->em->getRepository('App:Vehicle')->find($vehicle_id);
        } else {
            $vehicle = null;
        }

        $fuels = $this->em->getRepository('App:VehicleFuel')->findBetweenDate($fromDate, $toDate, $vehicle, $limit);

        if($returnObject) return $fuels;

        $result = [];
        foreach($fuels as $fuel)
        {
            $result[] = $fuel->toArray("light");
        }
        if(!$result) $result = ['message' => 'aucune donnée trouvée ce jour'];
        return $result;
    }

    /***** action methods **************************************/

    public function addAction(string $data)
    {
        $data = json_decode($data, true);
        $staff = $this->em->getRepository('App:Staff')->find($data['staff_id']);
        $vehicle = $this->em->getRepository('App:Vehicle')->find($data['vehicle_id']);

        if($staff == null || $vehicle == null) {
            return [
                            "message" => "Staff non trouvé et/ou véhicule non trouvé"
                    ];
        }

        $object = new VehicleAction();
        $object->setVehicle($vehicle);
        $object->setStaff($staff);
        $object->setQuantity($data['quantity']);
        $object->setAmount($data['amount']);
        $object->setMileage($data['mileage']);
        $object->setDateAction($data['date_action']);
        $object->setActionName($data['action_name']);
        $object->setActionType($data['action_type']);

        $this->mainService->create($object);
        $this->mainService->persist($object);

        $vehicle->setMileage($object->getMileage());

        $this->mainService->create($vehicle);
        $this->mainService->persist($vehicle);

        //Returns data
        return array(
            'status' => true,
            'message' => 'données ajoutées',
            'action' => $object->toArray(),
        );
    }

    public function listActionByDate($date = null, $vehicle_id = null)
    {
        if($vehicle_id) {
            $vehicle = $this->em->getRepository('App:Vehicle')->find($vehicle_id);
            $actions = $this->em->getRepository('App:VehicleAction')->findBy(['dateAction' => new DateTime($date), 'vehicle' => $vehicle], ['id' => 'desc']);
        } else {
          $actions = $this->em->getRepository('App:VehicleAction')->findBy(['dateAction' => new DateTime($date)], ['id' => 'desc']);
        }
        $result = [];
        foreach($actions as $action)
        {
            $result[] = $action->toArray("light");
        }
        if(!$result) $result = ['message' => 'aucune donnée trouvée ce jour'];
        return $result;
    }

    public function listActionByVehicle($vehicle_id = null, $limit = 100)
    {
        $vehicle = $this->em->getRepository('App:Vehicle')->find($vehicle_id);
        $actions = $this->em->getRepository('App:VehicleAction')->findBy(['vehicle' => $vehicle], ['id' => 'desc'], $limit);

        $result = [];
        foreach($actions as $action)
        {
            $result[] = $action->toArray("light");
        }
        if(!$result) $result = ['message' => 'aucune donnée trouvée ce jour'];
        return $result;
    }


    public function listActionBetweenDate($from, $to, $vehicle_id = null, $limit = null, $returnObject = false)
    {

        $fromDate = new DateTime($from);
        $toDate = new DateTime($to);

        if($vehicle_id) {
            $vehicle = $this->em->getRepository('App:Vehicle')->find($vehicle_id);
        } else {
            $vehicle = null;
        }

        $actions = $this->em->getRepository('App:VehicleAction')->findBetweenDate($fromDate, $toDate, $vehicle, $limit);

        if($returnObject) return $actions;

        $result = [];
        foreach($actions as $action)
        {
            $result[] = $action->toArray("light");
        }
        if(!$result) $result = ['message' => 'aucune donnée trouvée ce jour'];
        return $result;
    }






    /**
     * {@inheritdoc}
     */
    public function toArray(Vehicle $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        return $objectArray;
    }
}

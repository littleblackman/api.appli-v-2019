<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Entity\VehicleFuel;
use App\Form\VehicleType;
use App\Entity\VehicleCheckup;
use App\Service\VehicleServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * VehicleFuelController class
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class VehicleFuelController extends AbstractController
{
    private $vehicleService;

    public function __construct(VehicleServiceInterface $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

//ADD
    /**
     * Add fuel to a Vehicle
     *
     * @Route("/vehicle/add/fuel",
     *    name="vehicle_add_fuel",
     *    methods={"HEAD", "POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="vehicle", ref=@Model(type=VehicleFuel::class)),
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Tag(name="VehicleFuel")
     */
    public function addFuel(Request $request)
    {
        $this->denyAccessUnlessGranted('vehicleList');

        $createdData = $this->vehicleService->addFuel($request->getContent());

        return new JsonResponse($createdData);
    }


// ajouter la liste des prises d'essence par vehicle_id
// ajouter une plage de date > moyenne

// ajout de la photo du reçue de l'essence


//LIST
    /**
     * Lists all the vehicle fuel by date
     *
     * @Route("/vehicle/fuel/{date_action}/{vehicle_id}",
     *    name="vehicle_fuel_list_date",
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=VehicleFuel::class))
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="date",
     *     in="path",
     *     description="date action can be null",
     *     type="string",
     *     default="null"
     * )
     * @SWG\Parameter(
     *     name="vehicle_id",
     *     in="path",
     *     description="vehicle  can be null",
     *     type="integer",
     *     default="null"
     * )
     * @SWG\Tag(name="VehicleFuel")
     */
    public function listByDate(Request $request, $date_action = null, $vehicle_id = null)
    {
        $this->denyAccessUnlessGranted('vehicleList');

        $action = $this->vehicleService->listFuelByDate($date_action, $vehicle_id);

        return new JsonResponse($action);
    }

}

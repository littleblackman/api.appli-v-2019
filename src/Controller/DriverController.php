<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Form\DriverPriorityType;
use App\Form\DriverType;
use App\Service\DriverServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * DriverController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverController extends AbstractController
{
    private $driverService;

    public function __construct(DriverServiceInterface $driverService)
    {
        $this->driverService = $driverService;
    }

//LIST

    /**
     * Lists all the drivers
     *
     * @Route("/driver/list",
     *    name="driver_list",
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Driver::class))
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     description="Number of the page",
     *     type="integer",
     *     default="1",
     * )
     * @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="Number of records",
     *     type="integer",
     *     default="50",
     * )
     * @SWG\Tag(name="Driver")
     */
    public function listAll(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('driverList');

        $drivers = $paginator->paginate(
            $this->driverService->findAll(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $driversArray = array();
        foreach ($drivers->getItems() as $driver) {
            $driversArray[] = $this->driverService->toArray($driver);
        };

        return new JsonResponse($driversArray);
    }

//PRIORITY

    /**
     * Modifies priorities for Drivers
     *
     * @Route("/driver/priority",
     *    name="driver_priority",
     *    methods={"HEAD", "PUT"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *     )
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Driver",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=DriverPriorityType::class))
     *     )
     * )
     * @SWG\Tag(name="Driver")
     */
    public function priority(Request $request)
    {
        $this->denyAccessUnlessGranted('driverModify', null);

        $sortOrderData = $this->driverService->priority($request->getContent());

        return new JsonResponse($sortOrderData);
    }

//DISPLAY

    /**
     * Displays driver using its id
     *
     * @Route("/driver/display/{driverId}",
     *    name="driver_display",
     *    requirements={"driverId": "^([0-9]+)"},
     *    methods={"HEAD", "GET"})
     * @Entity("driver", expr="repository.findOneById(driverId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Driver::class))
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Not Found",
     * )
     * @SWG\Parameter(
     *     name="driverId",
     *     in="path",
     *     required=true,
     *     description="Id of the driver",
     *     type="integer",
     * )
     * @SWG\Tag(name="Driver")
     */
    public function display(Driver $driver)
    {
        $this->denyAccessUnlessGranted('driverDisplay', $driver);

        $driverArray = $this->driverService->toArray($driver);

        return new JsonResponse($driverArray);
    }

//CREATE

    /**
     * Creates a Driver
     *
     * @Route("/driver/create",
     *    name="driver_create",
     *    methods={"HEAD", "POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="driver", ref=@Model(type=Driver::class)),
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Driver",
     *     required=true,
     *     @Model(type=DriverType::class)
     * )
     * @SWG\Tag(name="Driver")
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('driverCreate', null);

        $createdData = $this->driverService->create($request->getContent());

        return new JsonResponse($createdData);
    }

//MODIFY

    /**
     * Modifies driver
     *
     * @Route("/driver/modify/{driverId}",
     *    name="driver_modify",
     *    requirements={"driverId": "^([0-9]+)"},
     *    methods={"HEAD", "PUT"})
     * @Entity("driver", expr="repository.findOneById(driverId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="driver", ref=@Model(type=Driver::class)),
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Not Found",
     * )
     * @SWG\Parameter(
     *     name="driverId",
     *     in="path",
     *     required=true,
     *     description="Id of the driver",
     *     type="integer",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Driver",
     *     required=true,
     *     @Model(type=DriverType::class)
     * )
     * @SWG\Tag(name="Driver")
     */
    public function modify(Request $request, Driver $driver)
    {
        $this->denyAccessUnlessGranted('driverModify', $driver);

        $modifiedData = $this->driverService->modify($driver, $request->getContent());

        return new JsonResponse($modifiedData);
    }

//DELETE

    /**
     * Deletes driver
     *
     * @Route("/driver/delete/{driverId}",
     *    name="driver_delete",
     *    requirements={"driverId": "^([0-9]+)"},
     *    methods={"HEAD", "DELETE"})
     * @Entity("driver", expr="repository.findOneById(driverId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Not Found",
     * )
     * @SWG\Parameter(
     *     name="driverId",
     *     in="path",
     *     required=true,
     *     description="Id of the driver",
     *     type="integer",
     * )
     * @SWG\Tag(name="Driver")
     */
    public function delete(Driver $driver)
    {
        $this->denyAccessUnlessGranted('driverDelete', $driver);

        $suppressedData = $this->driverService->delete($driver);

        return new JsonResponse($suppressedData);
    }
}

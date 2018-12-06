<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use App\Service\PickupServiceInterface;
use App\Entity\Pickup;
use App\Form\PickupType;

/**
 * PickupController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PickupController extends AbstractController
{
    private $pickupService;

    public function __construct(PickupServiceInterface $pickupService)
    {
        $this->pickupService = $pickupService;
    }

//LIST BY STATUS AND DATE
    /**
     * Lists all the pickups by date and status
     *
     * @Route("/pickup/list/{date}/{status}",
     *    name="pickup_list_status",
     *    requirements={
     *        "date": "^(([0-9]{4}-[0-9]{2}-[0-9]{2})|([0-9]{4}-[0-9]{2}))$",
     *        "status": "^(absent|supported|null)$"
     *    },
     *    defaults={"status": "null"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Pickup::class))
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="date",
     *     in="path",
     *     description="Date for the pickup (YYYY-MM-DD | YYYY-MM)",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="status",
     *     in="path",
     *     description="absent|supported|null pickups",
     *     type="string",
     *     default="null",
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     description="Number of the page",
     *     type="integer",
     * )
     * @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="Number of records",
     *     type="integer",
     * )
     * @SWG\Tag(name="Pickup")
     */
    public function listByStatus(Request $request, PaginatorInterface $paginator, $date, $status)
    {
        $this->denyAccessUnlessGranted('pickupList');

        $pickups = $paginator->paginate(
            $this->pickupService->findAllByStatus($date, $status),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $pickupsArray = array();
        foreach ($pickups->getItems() as $pickup) {
            $pickupsArray[] = $this->pickupService->toArray($pickup);
        };

        return new JsonResponse($pickupsArray);
    }

//LIST NOT AFFECTED
    /**
     * Lists all the pickups by date not affected to a ride
     *
     * @Route("/pickup/list/{date}/unaffected",
     *    name="pickup_list_unaffected",
     *    requirements={"date": "^(([0-9]{4}-[0-9]{2}-[0-9]{2})|([0-9]{4}-[0-9]{2}))$"},
     *    defaults={"status": "null"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Pickup::class))
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="date",
     *     in="path",
     *     description="Date for the pickup (YYYY-MM-DD | YYYY-MM)",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     description="Number of the page",
     *     type="integer",
     * )
     * @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="Number of records",
     *     type="integer",
     * )
     * @SWG\Tag(name="Pickup")
     */
    public function listUnaffected(Request $request, PaginatorInterface $paginator, $date)
    {
        $this->denyAccessUnlessGranted('pickupList');

        $pickups = $paginator->paginate(
            $this->pickupService->findAllUnaffected($date),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $pickupsArray = array();
        foreach ($pickups->getItems() as $pickup) {
            $pickupsArray[] = $this->pickupService->toArray($pickup);
        };

        return new JsonResponse($pickupsArray);
    }

//DISPLAY
    /**
     * Displays pickup
     *
     * @Route("/pickup/display/{pickupId}",
     *    name="pickup_display",
     *    requirements={"pickupId": "^([0-9]+)"},
     *    methods={"HEAD", "GET"})
     * @Entity("pickup", expr="repository.findOneById(pickupId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Pickup::class))
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
     *     name="pickupId",
     *     in="path",
     *     required=true,
     *     description="Id of the pickup",
     *     type="integer",
     * )
     * @SWG\Tag(name="Pickup")
     */
    public function display(Pickup $pickup)
    {
        $this->denyAccessUnlessGranted('pickupDisplay', $pickup);

        $pickupArray = $this->pickupService->toArray($pickup);

        return new JsonResponse($pickupArray);
    }

//SORT ORDER
    /**
     * Modifies sort order for Pickups
     *
     * @Route("/pickup/sort-order",
     *    name="pickup_sort_order",
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
     *     description="Data for the Pickup",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(@SWG\Property(property="pickupId", type="integer")),
     *         @SWG\Items(@SWG\Property(property="sortOrder", type="integer"))
     *     )
     * )
     * @SWG\Tag(name="Pickup")
     */
    public function sortOrder(Request $request)
    {
        $this->denyAccessUnlessGranted('pickupModify', null);

        $sortOrderData = $this->pickupService->sortOrder($request->getContent());

        return new JsonResponse($sortOrderData);
    }

//CREATE
    /**
     * Creates a Pickup
     *
     * @Route("/pickup/create",
     *    name="pickup_create",
     *    methods={"HEAD", "POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="pickup", ref=@Model(type=Pickup::class)),
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Pickup",
     *     required=true,
     *     @Model(type=PickupType::class)
     * )
     * @SWG\Tag(name="Pickup")
     */
    public function create(Request $request)
    {
        $pickup = new Pickup();
        $this->denyAccessUnlessGranted('pickupCreate', $pickup);

        $createdData = $this->pickupService->create($pickup, $request->getContent());

        return new JsonResponse($createdData);
    }

//MODIFY
    /**
     * Modifies pickup
     *
     * @Route("/pickup/modify/{pickupId}",
     *    name="pickup_modify",
     *    requirements={"pickupId": "^([0-9]+)"},
     *    methods={"HEAD", "PUT"})
     * @Entity("pickup", expr="repository.findOneById(pickupId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="pickup", ref=@Model(type=Pickup::class)),
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
     *     name="pickupId",
     *     in="path",
     *     required=true,
     *     description="Id of the pickup",
     *     type="integer",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Pickup",
     *     required=true,
     *     @Model(type=PickupType::class)
     * )
     * @SWG\Tag(name="Pickup")
     */
    public function modify(Request $request, Pickup $pickup)
    {
        $this->denyAccessUnlessGranted('pickupModify', $pickup);

        $modifiedData = $this->pickupService->modify($pickup, $request->getContent());

        return new JsonResponse($modifiedData);
    }

//DELETE
    /**
     * Deletes pickup
     *
     * @Route("/pickup/delete/{pickupId}",
     *    name="pickup_delete",
     *    requirements={"pickupId": "^([0-9]+)"},
     *    methods={"HEAD", "DELETE"})
     * @Entity("pickup", expr="repository.findOneById(pickupId)")
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
     *     name="pickupId",
     *     in="path",
     *     required=true,
     *     description="Id of the pickup",
     *     type="integer",
     * )
     * @SWG\Tag(name="Pickup")
     */
    public function delete(Pickup $pickup)
    {
        $this->denyAccessUnlessGranted('pickupDelete', $pickup);

        $suppressedData = $this->pickupService->delete($pickup);

        return new JsonResponse($suppressedData);
    }
}

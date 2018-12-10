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
use App\Service\PersonServiceInterface;
use App\Service\RideServiceInterface;
use App\Entity\Person;
use App\Entity\Ride;
use App\Form\RideType;

/**
 * RideController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class RideController extends AbstractController
{
    private $personService;
    private $rideService;

    public function __construct(
        PersonServiceInterface $personService,
        RideServiceInterface $rideService
    )
    {
        $this->personService = $personService;
        $this->rideService = $rideService;
    }

//LIST
    /**
     * Lists all the rides coming or finished
     *
     * @Route("/ride/list/{status}",
     *    name="ride_list",
     *    requirements={"status": "^(coming|finished)$"},
     *    defaults={"status": "coming"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Ride::class))
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="status",
     *     in="path",
     *     description="coming|finished rides",
     *     type="string",
     *     default="coming",
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
     * @SWG\Tag(name="Ride")
     */
    public function listAllByStatus(Request $request, PaginatorInterface $paginator, $status)
    {
        $this->denyAccessUnlessGranted('rideList');

        $rides = $paginator->paginate(
            $this->rideService->findAllByStatus($status),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $ridesArray = array();
        foreach ($rides->getItems() as $ride) {
            $ridesArray[] = $this->rideService->toArray($ride);
        };

        return new JsonResponse($ridesArray);
    }

//LIST BY DATE
    /**
     * Lists all the rides for a specific date
     *
     * @Route("/ride/list/{date}",
     *    name="ride_list_date",
     *    requirements={"date": "^(([0-9]{4}-[0-9]{2}-[0-9]{2})|([0-9]{4}-[0-9]{2}))$"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Ride::class))
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="date",
     *     in="path",
     *     description="Date for the ride (YYYY-MM-DD | YYYY-MM)",
     *     type="string",
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
     * @SWG\Tag(name="Ride")
     */
    public function listByDate(Request $request, PaginatorInterface $paginator, $date)
    {
        $this->denyAccessUnlessGranted('rideList');

        $rides = $paginator->paginate(
            $this->rideService->findAllByDate($date),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $ridesArray = array();
        foreach ($rides->getItems() as $ride) {
            $ridesArray[] = $this->rideService->toArray($ride);
        };

        return new JsonResponse($ridesArray);
    }

//DISPLAY BY DATE AND PERSONID
    /**
     * Displays the rides for a specific date and person
     *
     * @Route("/ride/display/{date}/{personId}",
     *    name="ride_display_date_person",
     *    requirements={"date": "^([0-9]{4}-[0-9]{2}-[0-9]{2})$"},
     *    requirements={"personId": "^([0-9]+)$"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Ride::class))
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="date",
     *     in="path",
     *     description="Date for the ride (YYYY-MM-DD)",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="personId",
     *     in="path",
     *     description="Id for the Person",
     *     type="string",
     * )
     * @SWG\Tag(name="Ride")
     */
    public function displayByDateAndPerson($date, $personId)
    {

        $this->denyAccessUnlessGranted('rideDisplay');

        $ridesArray = array();
        $person = $this->personService->findOneById($personId);
        if ($person instanceof Person) {
            $rides = $this->rideService->findAllByDateByPersonId($date, $person);
            foreach ($rides as $ride) {
                $ridesArray[] = $this->rideService->toArray($ride);
            };
        }

        return new JsonResponse($ridesArray);
    }

//DISPLAY BY ID
    /**
     * Displays the ride using its id
     *
     * @Route("/ride/display/{rideId}",
     *    name="ride_list_id",
     *    requirements={"date": "^([0-9]+)$"},
     *    methods={"HEAD", "GET"})
     * @Entity("ride", expr="repository.findOneById(rideId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Ride::class))
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
     *     name="rideId",
     *     in="path",
     *     description="Id for the ride",
     *     type="string",
     * )
     * @SWG\Tag(name="Ride")
     */
    public function display(Ride $ride)
    {
        $this->denyAccessUnlessGranted('rideDisplay', $ride);

        $rideArray = $this->rideService->toArray($ride);

        return new JsonResponse($rideArray);
    }

//CREATE
    /**
     * Creates a Ride
     *
     * @Route("/ride/create",
     *    name="ride_create",
     *    methods={"HEAD", "POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="ride", ref=@Model(type=Ride::class)),
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Ride",
     *     required=true,
     *     @Model(type=RideType::class)
     * )
     * @SWG\Tag(name="Ride")
     */
    public function create(Request $request)
    {
        $ride = new Ride();
        $this->denyAccessUnlessGranted('rideCreate', $ride);

        $createdData = $this->rideService->create($ride, $request->getContent());

        return new JsonResponse($createdData);
    }

//MODIFY
    /**
     * Modifies ride
     *
     * @Route("/ride/modify/{rideId}",
     *    name="ride_modify",
     *    requirements={"rideId": "^([0-9]+)"},
     *    methods={"HEAD", "PUT"})
     * @Entity("ride", expr="repository.findOneById(rideId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="ride", ref=@Model(type=Ride::class)),
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
     *     name="rideId",
     *     in="path",
     *     required=true,
     *     description="Id of the ride",
     *     type="integer",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Ride",
     *     required=true,
     *     @Model(type=RideType::class)
     * )
     * @SWG\Tag(name="Ride")
     */
    public function modify(Request $request, Ride $ride)
    {
        $this->denyAccessUnlessGranted('rideModify', $ride);

        $modifiedData = $this->rideService->modify($ride, $request->getContent());

        return new JsonResponse($modifiedData);
    }

//DELETE
    /**
     * Deletes ride and moves all the pickups as "Non pris en charge"
     *
     * @Route("/ride/delete/{rideId}",
     *    name="ride_delete",
     *    requirements={"rideId": "^([0-9]+)"},
     *    methods={"HEAD", "DELETE"})
     * @Entity("ride", expr="repository.findOneById(rideId)")
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
     *     name="rideId",
     *     in="path",
     *     required=true,
     *     description="Id of the ride",
     *     type="integer",
     * )
     * @SWG\Tag(name="Ride")
     */
    public function delete(Ride $ride)
    {
        $this->denyAccessUnlessGranted('rideDelete', $ride);

        $suppressedData = $this->rideService->delete($ride);

        return new JsonResponse($suppressedData);
    }
}

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
use App\Service\DriverPresenceServiceInterface;
use App\Entity\DriverPresence;
use App\Form\DriverPresenceType;

/**
 * DriverPresenceController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverPresenceController extends AbstractController
{
    private $driverPresenceService;

    public function __construct(DriverPresenceServiceInterface $driverPresenceService)
    {
        $this->driverPresenceService = $driverPresenceService;
    }

//LIST
    /**
     * Lists all the driver presences by date
     *
     * @Route("/driver/presence/list/{date}",
     *    name="driver_presence_list_date",
     *    requirements={"date": "^(([0-9]{4}-[0-9]{2}-[0-9]{2})|([0-9]{4}-[0-9]{2}))$"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=DriverPresence::class))
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="date",
     *     in="path",
     *     description="Date for the driver presence (YYYY-MM-DD | YYYY-MM)",
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
     * @SWG\Tag(name="DriverPresence")
     */
    public function listAll(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('driverPresenceList');

        $driverPresences = $paginator->paginate(
            $this->driverPresenceService->findAll(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $driverPresencesArray = array();
        foreach ($driverPresences->getItems() as $driverPresence) {
            $driverPresencesArray[] = $this->driverPresenceService->toArray($driverPresence);
        };

        return new JsonResponse($driverPresencesArray);
    }

//DISPLAY
    /**
     * Displays driverPresence using driverId and date (optional)
     *
     * @Route("/driver/presence/display/{driverId}/{date}",
     *    name="driver_presence_display",
     *    requirements={
     *        "driverId": "^([0-9]+)",
     *        "date": "^(([0-9]{4}-[0-9]{2}-[0-9]{2})|([0-9]{4}-[0-9]{2}))$"
     *    },
     *    defaults={"date": null},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=DriverPresence::class))
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
     *     name="date",
     *     in="path",
     *     description="Date for the driver presence (YYYY-MM-DD | YYYY-MM)",
     *     type="string",
     *     default="null",
     * )
     * @SWG\Tag(name="DriverPresence")
     */
    public function display($driverId, $date)
    {
        $this->denyAccessUnlessGranted('driverPresenceDisplay', null);

        $driverPresencesArray = array();
        foreach ($this->driverPresenceService->findByDriver($driverId, $date) as $driverPresence) {
            $driverPresencesArray[] = $this->driverPresenceService->toArray($driverPresence);
        };

        return new JsonResponse($driverPresencesArray);
    }

//CREATE
    /**
     * Creates a DriverPresence
     *
     * @Route("/driver/presence/create",
     *    name="driver_presence_create",
     *    methods={"HEAD", "POST"})
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
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the DriverPresence",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=DriverPresenceType::class))
     *     )
     * )
     * @SWG\Tag(name="DriverPresence")
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('driverPresenceCreate', null);

        $createdData = $this->driverPresenceService->create($request->getContent());

        return new JsonResponse($createdData);
    }

//DELETE BY ID
    /**
     * Deletes driverPresence using its id
     *
     * @Route("/driver/presence/delete/{driverPresenceId}",
     *    name="driver_presence_delete",
     *    requirements={"driverPresenceId": "^([0-9]+)"},
     *    methods={"HEAD", "DELETE"})
     * @Entity("component", expr="repository.findOneById(driverPresenceId)")
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
     *     name="driverPresenceId",
     *     in="path",
     *     description="Id for the DriverPresence",
     *     required=true,
     *     type="integer",
     * )
     * @SWG\Tag(name="DriverPresence")
     */
    public function delete(Request $request, DriverPresence $driverPresence)
    {
        $this->denyAccessUnlessGranted('driverPresenceDelete', $driverPresence);

        $suppressedData = $this->driverPresenceService->delete($driverPresence, $request->getContent());

        return new JsonResponse($suppressedData);
    }

//DELETE BY ARRAY OF IDS
    /**
     * Deletes driverPresence
     *
     * @Route("/driver/presence/delete",
     *    name="driver_presence_delete_by_array",
     *    methods={"HEAD", "DELETE"})
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
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the DriverPresence",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=DriverPresenceType::class))
     *     )
     * )
     * @SWG\Tag(name="DriverPresence")
     */
    public function deleteByArray(Request $request)
    {
        $this->denyAccessUnlessGranted('driverPresenceDelete', null);

        $suppressedData = $this->driverPresenceService->deleteByArray($request->getContent());

        return new JsonResponse($suppressedData);
    }
}

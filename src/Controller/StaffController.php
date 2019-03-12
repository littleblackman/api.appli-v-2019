<?php

namespace App\Controller;

use App\Entity\Staff;
use App\Form\DriverPriorityType;
use App\Form\StaffType;
use App\Service\StaffServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * StaffController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class StaffController extends AbstractController
{
    private $staffService;

    public function __construct(StaffServiceInterface $staffService)
    {
        $this->staffService = $staffService;
    }

//LIST
    /**
     * Lists all the staffs
     *
     * @Route("/staff/list/{kind}",
     *    name="staff_list",
     *    requirements={"kind": "^([a-zA-Z]+)"},
     *    defaults={"kind": "all"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Staff::class))
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="kind",
     *     in="path",
     *     description="Kind of staff",
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
     * @SWG\Tag(name="Staff")
     */
    public function listAll(Request $request, $kind, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('staffList');

        $staffs = $paginator->paginate(
            $this->staffService->findAllByKind($kind),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $staffsArray = array();
        foreach ($staffs->getItems() as $staff) {
            $staffsArray[] = $this->staffService->toArray($staff);
        };

        return new JsonResponse($staffsArray);
    }

//SEARCH
    /**
     * Searches for %{term}% in firstname|lastname for Staff
     *
     * @Route("/staff/search/{term}",
     *    name="staff_search",
     *    requirements={"term": "^([a-zA-Z0-9\ \-]+)"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Staff::class))
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
     *     name="term",
     *     in="path",
     *     required=true,
     *     description="Searched term",
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
     * @SWG\Tag(name="Staff")
     */
    public function search(Request $request, PaginatorInterface $paginator, string $term)
    {
        $this->denyAccessUnlessGranted('staffList');
        $staffs = $paginator->paginate(
            $this->staffService->findAllSearch($term),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $staffsArray = array();
        foreach ($staffs->getItems() as $staff) {
            $staffsArray[] = $this->staffService->toArray($staff);
        };

        return new JsonResponse($staffsArray);
    }

//DISPLAY
    /**
     * Displays staff using its id
     *
     * @Route("/staff/display/{staffId}",
     *    name="staff_display",
     *    requirements={"staffId": "^([0-9]+)$"},
     *    methods={"HEAD", "GET"})
     * @Entity("staff", expr="repository.findOneById(staffId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Staff::class))
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
     *     name="staffId",
     *     in="path",
     *     required=true,
     *     description="Id of the staff",
     *     type="integer",
     * )
     * @SWG\Tag(name="Staff")
     */
    public function display(Staff $staff)
    {
        $this->denyAccessUnlessGranted('staffDisplay', $staff);

        $staffArray = $this->staffService->toArray($staff);

        return new JsonResponse($staffArray);
    }

//CREATE
    /**
     * Creates a Staff
     *
     * @Route("/staff/create",
     *    name="staff_create",
     *    methods={"HEAD", "POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="staff", ref=@Model(type=Staff::class)),
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Staff",
     *     required=true,
     *     @Model(type=StaffType::class)
     * )
     * @SWG\Tag(name="Staff")
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('staffCreate');

        $createdData = $this->staffService->create($request->getContent());

        return new JsonResponse($createdData);
    }

//MODIFY
    /**
     * Modifies staff
     *
     * @Route("/staff/modify/{staffId}",
     *    name="staff_modify",
     *    requirements={"staffId": "^([0-9]+)$"},
     *    methods={"HEAD", "PUT"})
     * @Entity("staff", expr="repository.findOneById(staffId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="staff", ref=@Model(type=Staff::class)),
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
     *     name="staffId",
     *     in="path",
     *     required=true,
     *     description="Id of the staff",
     *     type="integer",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Staff",
     *     required=true,
     *     @Model(type=StaffType::class)
     * )
     * @SWG\Tag(name="Staff")
     */
    public function modify(Request $request, Staff $staff)
    {
        $this->denyAccessUnlessGranted('staffModify', $staff);

        $modifiedData = $this->staffService->modify($staff, $request->getContent());

        return new JsonResponse($modifiedData);
    }

//DELETE
    /**
     * Deletes staff
     *
     * @Route("/staff/delete/{staffId}",
     *    name="staff_delete",
     *    requirements={"staffId": "^([0-9]+)$"},
     *    methods={"HEAD", "DELETE"})
     * @Entity("staff", expr="repository.findOneById(staffId)")
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
     *     name="staffId",
     *     in="path",
     *     required=true,
     *     description="Id of the staff",
     *     type="integer",
     * )
     * @SWG\Tag(name="Staff")
     */
    public function delete(Staff $staff)
    {
        $this->denyAccessUnlessGranted('staffDelete', $staff);

        $suppressedData = $this->staffService->delete($staff);

        return new JsonResponse($suppressedData);
    }

//PRIORITY
    /**
     * Modifies priorities for Staffs
     *
     * @Route("/staff/priority",
     *    name="staff_priority",
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
     *     description="Data for the Staff",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=DriverPriorityType::class))
     *     )
     * )
     * @SWG\Tag(name="Staff")
     */
    public function priority(Request $request)
    {
        $this->denyAccessUnlessGranted('staffModify');

        $sortOrderData = $this->staffService->priority($request->getContent());

        return new JsonResponse($sortOrderData);
    }
}

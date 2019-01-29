<?php

namespace App\Controller;

use App\Entity\GroupActivity;
use App\Entity\Staff;
use App\Form\GroupActivityType;
use App\Service\GroupActivityServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * GroupActivityController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class GroupActivityController extends AbstractController
{
    private $groupActivityService;

    public function __construct(
        GroupActivityServiceInterface $groupActivityService
    )
    {
        $this->groupActivityService = $groupActivityService;
    }

//LIST BY DATE

    /**
     * Lists all the groupActivities for a specific date
     *
     * @Route("/group-activity/list/{date}",
     *    name="groupActivity_list_date",
     *    requirements={"date": "^(([0-9]{4}-[0-9]{2}-[0-9]{2})|([0-9]{4}-[0-9]{2}))$"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=GroupActivity::class))
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="date",
     *     in="path",
     *     description="Date for the groupActivity (YYYY-MM-DD | YYYY-MM)",
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
     * @SWG\Tag(name="GroupActivity")
     */
    public function listByDate(Request $request, PaginatorInterface $paginator, $date)
    {
        $this->denyAccessUnlessGranted('groupActivityList');

        $groupActivities = $paginator->paginate(
            $this->groupActivityService->findAllByDate($date),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $groupActivitiesArray = array();
        foreach ($groupActivities->getItems() as $groupActivity) {
            $groupActivitiesArray[] = $this->groupActivityService->toArray($groupActivity);
        };

        return new JsonResponse($groupActivitiesArray);
    }

//DISPLAY BY ID

    /**
     * Displays the groupActivity using its id
     *
     * @Route("/group-activity/display/{groupActivityId}",
     *    name="groupActivity_list_id",
     *    requirements={"groupActivityId": "^([0-9]+)$"},
     *    methods={"HEAD", "GET"})
     * @Entity("groupActivity", expr="repository.findOneById(groupActivityId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=GroupActivity::class))
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
     *     name="groupActivityId",
     *     in="path",
     *     description="Id for the groupActivity",
     *     type="string",
     * )
     * @SWG\Tag(name="GroupActivity")
     */
    public function display(GroupActivity $groupActivity)
    {
        $this->denyAccessUnlessGranted('groupActivityDisplay', $groupActivity);

        $groupActivityArray = $this->groupActivityService->toArray($groupActivity);

        return new JsonResponse($groupActivityArray);
    }

//CREATE

    /**
     * Creates a GroupActivity
     *
     * @Route("/group-activity/create",
     *    name="groupActivity_create",
     *    methods={"HEAD", "POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="groupActivity", ref=@Model(type=GroupActivity::class)),
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the GroupActivity",
     *     required=true,
     *     @Model(type=GroupActivityType::class)
     * )
     * @SWG\Tag(name="GroupActivity")
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('groupActivityCreate', null);

        $createdData = $this->groupActivityService->create($request->getContent());

        return new JsonResponse($createdData);
    }

//CREATE MULTIPLE

    /**
     * Creates multiples GroupActivitys
     *
     * @Route("/group-activity/create-multiple",
     *    name="groupActivity_create_multiple",
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
     *     description="Data for the GroupActivitys",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=GroupActivityType::class))
     *     )
     * )
     * @SWG\Tag(name="GroupActivity")
     */
    public function createMultiple(Request $request)
    {
        $this->denyAccessUnlessGranted('groupActivityCreate', null);

        $createdData = $this->groupActivityService->createMultiple($request->getContent());

        return new JsonResponse($createdData);
    }

//MODIFY

    /**
     * Modifies groupActivity
     *
     * @Route("/group-activity/modify/{groupActivityId}",
     *    name="groupActivity_modify",
     *    requirements={"groupActivityId": "^([0-9]+)"},
     *    methods={"HEAD", "PUT"})
     * @Entity("groupActivity", expr="repository.findOneById(groupActivityId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="groupActivity", ref=@Model(type=GroupActivity::class)),
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
     *     name="groupActivityId",
     *     in="path",
     *     required=true,
     *     description="Id of the groupActivity",
     *     type="integer",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the GroupActivity",
     *     required=true,
     *     @Model(type=GroupActivityType::class)
     * )
     * @SWG\Tag(name="GroupActivity")
     */
    public function modify(Request $request, GroupActivity $groupActivity)
    {
        $this->denyAccessUnlessGranted('groupActivityModify', $groupActivity);

        $modifiedData = $this->groupActivityService->modify($groupActivity, $request->getContent());

        return new JsonResponse($modifiedData);
    }

//DELETE

    /**
     * Deletes groupActivity and moves all the pickups as "Non pris en charge"
     *
     * @Route("/group-activity/delete/{groupActivityId}",
     *    name="groupActivity_delete",
     *    requirements={"groupActivityId": "^([0-9]+)"},
     *    methods={"HEAD", "DELETE"})
     * @Entity("groupActivity", expr="repository.findOneById(groupActivityId)")
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
     *     name="groupActivityId",
     *     in="path",
     *     required=true,
     *     description="Id of the groupActivity",
     *     type="integer",
     * )
     * @SWG\Tag(name="GroupActivity")
     */
    public function delete(GroupActivity $groupActivity)
    {
        $this->denyAccessUnlessGranted('groupActivityDelete', $groupActivity);

        $suppressedData = $this->groupActivityService->delete($groupActivity);

        return new JsonResponse($suppressedData);
    }
}

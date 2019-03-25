<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TaskStaff;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TaskStaff class
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class TaskStaffController extends AbstractController
{

    private $taskStaffService;

    public function __construct(StaffPresenceServiceInterface $taskStaffServic)
    {
        $this->$taskStaffService = $taskStaffService;
    }

//CREATE
    /**
     * Creates TaskStaff
     *
     * @Route("/task/staff/create",
     *    name="task_staff_create",
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
     *     description="Data for the TaskStaff",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=TaskStaffType::class))
     *     )
     * )
     * @SWG\Tag(name="TaskStaff")
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('taskStaffCreate');

        $createdData = $this->staffPresenceService->create($request->getContent());

        return new JsonResponse($createdData);
    }

}

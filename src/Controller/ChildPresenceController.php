<?php

namespace App\Controller;

use App\Entity\ChildPresence;
use App\Form\ChildPresenceType;
use App\Service\ChildPresenceServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ChildPresenceController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ChildPresenceController extends AbstractController
{
    private $childPresenceService;

    public function __construct(ChildPresenceServiceInterface $childPresenceService)
    {
        $this->childPresenceService = $childPresenceService;
    }

//LIST

    /**
     * Lists all the child presences by date
     *
     * @Route("/child/presence/list/{date}",
     *    name="child_presence_list_date",
     *    requirements={"date": "^(([0-9]{4}-[0-9]{2}-[0-9]{2})|([0-9]{4}-[0-9]{2}))$"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=ChildPresence::class))
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="date",
     *     in="path",
     *     description="Date for the child presence (YYYY-MM-DD | YYYY-MM)",
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
     * @SWG\Tag(name="ChildPresence")
     */
    public function listAll(Request $request, $date, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('childPresenceList');

        $childPresences = $paginator->paginate(
            $this->childPresenceService->findAllByDate($date),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $childPresencesArray = array();
        foreach ($childPresences->getItems() as $childPresence) {
            $childPresencesArray[] = $this->childPresenceService->toArray($childPresence);
        };

        return new JsonResponse($childPresencesArray);
    }

//DISPLAY

    /**
     * Displays childPresence using childId and date (optional)
     *
     * @Route("/child/presence/display/{childId}/{date}",
     *    name="child_presence_display",
     *    requirements={
     *        "childId": "^([0-9]+)",
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
     *         @SWG\Items(ref=@Model(type=ChildPresence::class))
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
     *     name="childId",
     *     in="path",
     *     required=true,
     *     description="Id of the child",
     *     type="integer",
     * )
     * @SWG\Parameter(
     *     name="date",
     *     in="path",
     *     description="Date for the child presence (YYYY-MM-DD | YYYY-MM optional)",
     *     type="string",
     *     default="null",
     * )
     * @SWG\Tag(name="ChildPresence")
     */
    public function display($childId, $date)
    {
        $this->denyAccessUnlessGranted('childPresenceDisplay', null);

        $childPresencesArray = array();
        foreach ($this->childPresenceService->findByChild($childId, $date) as $childPresence) {
            $childPresencesArray[] = $this->childPresenceService->toArray($childPresence);
        };

        return new JsonResponse($childPresencesArray);
    }

//CREATE

    /**
     * Creates ChildPresence
     *
     * @Route("/child/presence/create",
     *    name="child_presence_create",
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
     *     description="Data for the ChildPresence",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=ChildPresenceType::class))
     *     )
     * )
     * @SWG\Tag(name="ChildPresence")
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('childPresenceCreate', null);

        $createdData = $this->childPresenceService->create($request->getContent());

        return new JsonResponse($createdData);
    }

//DELETE BY ID

    /**
     * Deletes childPresence using its id
     *
     * @Route("/child/presence/delete/{childPresenceId}",
     *    name="child_presence_delete",
     *    requirements={"childPresenceId": "^([0-9]+)"},
     *    methods={"HEAD", "DELETE"})
     * @Entity("childPresence", expr="repository.findOneById(childPresenceId)")
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
     *     name="childPresenceId",
     *     in="path",
     *     description="Id for the ChildPresence",
     *     required=true,
     *     type="integer",
     * )
     * @SWG\Tag(name="ChildPresence")
     */
    public function delete(ChildPresence $childPresence)
    {
        $this->denyAccessUnlessGranted('childPresenceDelete', $childPresence);

        $suppressedData = $this->childPresenceService->delete($childPresence);

        return new JsonResponse($suppressedData);
    }

//DELETE BY ARRAY OF IDS

    /**
     * Deletes childPresence
     *
     * @Route("/child/presence/delete",
     *    name="child_presence_delete_by_array",
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
     *     description="Data for the ChildPresence",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=ChildPresenceType::class))
     *     )
     * )
     * @SWG\Tag(name="ChildPresence")
     */
    public function deleteByArray(Request $request)
    {
        $this->denyAccessUnlessGranted('childPresenceDelete', null);

        $suppressedData = $this->childPresenceService->deleteByArray($request->getContent());

        return new JsonResponse($suppressedData);
    }

//DELETE BY REGISTRATION_ID

    /**
     * Deletes childPresence using the registrationId
     *
     * @Route("/child/presence/delete-registration/{registrationId}",
     *    name="child_presence_delete_by_registration",
     *    requirements={"registrationId": "^([0-9]+)"},
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
     * @SWG\Response(
     *     response=404,
     *     description="Not Found",
     * )
     * @SWG\Parameter(
     *     name="registrationId",
     *     in="path",
     *     required=true,
     *     description="RegistrationId linked to the childPresence",
     *     type="integer",
     * )
     * @SWG\Tag(name="ChildPresence")
     */
    public function deleteByRegistrationId(int $registrationId)
    {
        $this->denyAccessUnlessGranted('childPresenceDelete', null);

        $suppressedData = $this->childPresenceService->deleteByRegistrationId($registrationId);

        return new JsonResponse($suppressedData);
    }
}

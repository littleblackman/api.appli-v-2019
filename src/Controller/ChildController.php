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
use App\Service\ChildServiceInterface;
use App\Form\ChildType;
use App\Entity\Child;

/**
 * ChildController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ChildController extends AbstractController
{
    private $childService;

    public function __construct(ChildServiceInterface $childService)
    {
        $this->childService = $childService;
    }

//LIST
    /**
     * Lists all the children
     *
     * @Route("/child/list",
     *    name="child_list",
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="childId", type="integer"),
     *         @SWG\Property(property="firstname", type="string"),
     *         @SWG\Property(property="lastname", type="string"),
     *         @SWG\Property(property="birthdate", type="datetime"),
     *         @SWG\Property(property="photo", type="string"),
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
     * @SWG\Tag(name="Child")
     */
    public function listAll(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('childList');

        $children = $paginator->paginate(
            $this->childService->findAllInArray(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        return new JsonResponse($children->getItems());
    }

//SEARCH
    /**
     * Searches for %{term}% in firstname|lastname for Child
     *
     * @Route("/child/search/{term}",
     *    name="child_search",
     *    requirements={"term": "^([a-zA-Z]+)"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="childId", type="integer"),
     *         @SWG\Property(property="firstname", type="string"),
     *         @SWG\Property(property="lastname", type="string"),
     *         @SWG\Property(property="birthdate", type="datetime"),
     *         @SWG\Property(property="photo", type="string"),
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
     * @SWG\Tag(name="Child")
     */
    public function search(Request $request, PaginatorInterface $paginator, string $term)
    {
        $this->denyAccessUnlessGranted('childSearch');

        $children = $paginator->paginate(
            $this->childService->findAllInSearch($term),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        return new JsonResponse($children->getItems());
    }

//CREATE
    /**
     * Creates a child
     *
     * @Route("/child/create",
     *    name="child_create",
     *    methods={"HEAD", "POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="child", @Model(type=Child::class)),
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Child",
     *     required=true,
     *     @Model(type=ChildType::class)
     * )
     * @SWG\Tag(name="Child")
     */
    public function create(Request $request)
    {
        $child = new Child();
        $this->denyAccessUnlessGranted('childCreate', $child);

        $createdData = $this->childService->create($child, $request->getContent());

        return new JsonResponse($createdData);
    }

//DISPLAY
    /**
     * Displays child
     *
     * @Route("/child/display/{childId}",
     *    name="child_display",
     *    requirements={"childId": "^([0-9]+)"},
     *    methods={"HEAD", "GET"})
     * @Entity("child", expr="repository.findOneById(childId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=Child::class),
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
     * @SWG\Tag(name="Child")
     */
    public function display(Request $request, Child $child)
    {
        $this->denyAccessUnlessGranted('childDisplay', $child);

        $childArray = $this->childService->filter($child->toArray());

        return new JsonResponse($childArray);
    }

//MODIFY
    /**
     * Modifies child
     *
     * @Route("/child/modify/{childId}",
     *    name="child_modify",
     *    requirements={"childId": "^([0-9]+)"},
     *    methods={"HEAD", "PUT"})
     * @Entity("child", expr="repository.findOneById(childId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="child", @Model(type=Child::class)),
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
     *     name="data",
     *     in="body",
     *     description="Data for the Child",
     *     required=true,
     *     @Model(type=ChildType::class)
     * )
     * @SWG\Tag(name="Child")
     */
    public function modify(Request $request, Child $child)
    {
        $this->denyAccessUnlessGranted('childModify', $child);

        $modifiedData = $this->childService->modify($child, $request->getContent());

        return new JsonResponse($modifiedData);
    }

//DELETE
    /**
     * Deletes child
     *
     * @Route("/child/delete/{childId}",
     *    name="child_delete",
     *    requirements={"childId": "^([0-9]+)"},
     *    methods={"HEAD", "DELETE"})
     * @Entity("child", expr="repository.findOneById(childId)")
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
     *     name="childId",
     *     in="path",
     *     required=true,
     *     description="Id of the child",
     *     type="integer",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Child",
     *     required=true,
     *     @Model(type=ChildType::class)
     * )
     * @SWG\Tag(name="Child")
     */
    public function delete(Request $request, Child $child)
    {
        $this->denyAccessUnlessGranted('childDelete', $child);

        $suppressedData = $this->childService->delete($child, $request->getContent());

        return new JsonResponse($suppressedData);
    }
}

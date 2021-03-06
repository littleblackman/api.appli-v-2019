<?php

namespace App\Controller;

use App\Entity\Child;
use App\Form\ChildType;
use App\Service\ChildServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Child::class))
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
            $this->childService->findAll(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $childrenArray = array();
        foreach ($children->getItems() as $child) {
            $childrenArray[] = $this->childService->toArray($child);
        };

        return new JsonResponse($childrenArray);
    }

//SEARCH
    /**
     * Searches for %{term}% in firstname|lastname for Child
     *
     * @Route("/child/search/{term}",
     *    name="child_search",
     *    requirements={"term": "^([a-zA-Z0-9\ \-]+)"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Child::class))
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
        $this->denyAccessUnlessGranted('childList');

        $children = $paginator->paginate(
            $this->childService->findAllSearch($term),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $childrenArray = array();
        foreach ($children->getItems() as $child) {
            $childrenArray[] = $this->childService->toArray($child);
        };

        return new JsonResponse($childrenArray);
    }


//SEARCH
    /**
     * Searches for %{term}% in firstname|lastname for Child
     *
     * @Route("/child/fastsearch/{term}",
     *    name="child_fast_search",
     *    requirements={"term": "^([a-zA-Z0-9\ \-]+)"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Child::class))
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
    public function fastSearch(Request $request, string $term)
    {
        $this->denyAccessUnlessGranted('childList');

        $term = str_replace("27", "'", $term);
        $childArray = $this->childService->findFastSearch($term);

        return new JsonResponse($childArray);
    }

//DISPLAY
    /**
     * Displays child
     *
     * @Route("/child/display/{childId}",
     *    name="child_display",
     *    requirements={"childId": "^([0-9]+)$"},
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

        $childArray = $this->childService->toArray($child);

        return new JsonResponse($childArray);
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
     *         @SWG\Property(property="child", ref=@Model(type=Child::class)),
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
        //$this->denyAccessUnlessGranted('childCreate');

        $createdData = $this->childService->create($request->getContent());

        return new JsonResponse($createdData);
    }

//MODIFY
    /**
     * Modifies child
     *
     * @Route("/child/modify/{childId}",
     *    name="child_modify",
     *    requirements={"childId": "^([0-9]+)$"},
     *    methods={"HEAD", "PUT"})
     * @Entity("child", expr="repository.findOneById(childId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="child", ref=@Model(type=Child::class)),
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


//REMOVE LINK
    /**
     * Remove child link person
     *
     * @Route("/child/removePerson/{childId}/{personId}",
     *    name="child_remove_link_person",
     *    requirements={"childId": "^([0-9]+)$"},
     *    methods={"HEAD", "GET"})
     * @Entity("child", expr="repository.findOneById(childId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="child", ref=@Model(type=Child::class)),
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
    public function removePerson(Child $child, $personId)
    {
        $this->denyAccessUnlessGranted('childModify', $child);

        $modifiedData = $this->childService->removePerson($child, $personId);

        return new JsonResponse($modifiedData);
    }



//DELETE
    /**
     * Deletes child
     *
     * @Route("/child/delete/{childId}",
     *    name="child_delete",
     *    requirements={"childId": "^([0-9]+)$"},
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
    public function delete(Child $child)
    {
        $this->denyAccessUnlessGranted('childDelete', $child);

        $suppressedData = $this->childService->delete($child);

        return new JsonResponse($suppressedData);
    }


  //LIST BIRTHDATE
    /**
     * List child birthdate
     *
     * @Route("/child/birthdate",
     *    name="child_birthdate",
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Child::class))
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Tag(name="Child")
     */
    public function retrieveCurrentBirthdates(Request $request)
    {
        $this->denyAccessUnlessGranted('childList');

        $birthdates = $this->childService->retrieveCurrentBirthdates();

        return new JsonResponse($birthdates);
    }
}

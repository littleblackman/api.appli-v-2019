<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\ChildServiceInterface;
use App\Entity\Child;

class ChildController extends AbstractController
{
    private $childService;

    public function __construct(ChildServiceInterface $childService)
    {
        $this->childService = $childService;
    }

//LIST
    /**
     * List of all the children using "/child/list"
     * Optional: page(int) Number of the page /
     * Optionnal: size(int) Number of records
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/child/list",
     *    name="child_all",
     *    methods={"HEAD", "GET"})
     */
    public function all(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('childList');

        $children = $paginator->paginate(
            $this->childService->getAllInArray(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        return new JsonResponse($children->getItems());
    }

//DISPLAY
    /**
     * Specific child using "/child/display/{id}"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/child/display/{id}",
     *    name="child_display",
     *    requirements={"id": "^([0-9]+)"},
     *    methods={"HEAD", "GET"})
     */
    public function display(Child $child)
    {
        $this->denyAccessUnlessGranted('childDisplay', $child);

        return new JsonResponse($child->toArray());
    }

//MODIFY
    /**
     * Modify specific child using "/child/modify/{id}"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/child/modify/{id}",
     *    name="child_modify",
     *    requirements={"id": "^([0-9]+)"},
     *    methods={"HEAD", "POST"})
     */
    public function modify(Child $child)
    {
        $this->denyAccessUnlessGranted('childModify', $child);
dd('here');
        return new JsonResponse(array());
    }

//DELETE
    /**
     * Deletes specific child using "/child/delete/{id}"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/child/delete/{id}",
     *    name="child_delete",
     *    requirements={"id": "^([0-9]+)"},
     *    methods={"HEAD", "POST"})
     */
    public function delete(Child $child)
    {
        $this->denyAccessUnlessGranted('childDelete', $child);
dd('here');
        return new JsonResponse(array());
    }
}

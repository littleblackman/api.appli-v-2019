<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\PersonServiceInterface;
use App\Entity\Person;

class PersonController extends AbstractController
{
    private $personService;

    public function __construct(PersonServiceInterface $personService)
    {
        $this->personService = $personService;
    }

//LIST
    /**
     * List of all the persons using "/person/list".
     * Optional: page(int) Number of the page /
     * Optionnal: size(int) Number of records
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/person/list",
     *    name="person_all",
     *    methods={"HEAD", "GET"})
     */
    public function all(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('personList');

        $persons = $paginator->paginate(
            $this->personService->getAllInArray(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        return new JsonResponse($persons->getItems());
    }

//DISPLAY
    /**
     * Specific person using "/person/display/{id}"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/person/display/{id}",
     *    name="person_display",
     *    requirements={"id": "^([0-9]+)"},
     *    methods={"HEAD", "GET"})
     */
    public function display(Person $person)
    {
        $this->denyAccessUnlessGranted('personDisplay', $person);

        return new JsonResponse($person->toArray());
    }

//MODIFY
    /**
     * Modify specific person using "/person/modify/{id}"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/person/modify/{id}",
     *    name="person_modify",
     *    requirements={"id": "^([0-9]+)"},
     *    methods={"HEAD", "POST"})
     */
    public function modify(Person $person)
    {
        $this->denyAccessUnlessGranted('personModify', $person);
dd('here');
        return new JsonResponse(array());
    }

//DELETE
    /**
     * Deletes specific person using "/person/delete/{id}"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/person/delete/{id}",
     *    name="person_delete",
     *    requirements={"id": "^([0-9]+)"},
     *    methods={"HEAD", "POST"})
     */
    public function delete(Person $person)
    {
        $this->denyAccessUnlessGranted('personDelete', $person);
dd('here');
        return new JsonResponse(array());
    }
}

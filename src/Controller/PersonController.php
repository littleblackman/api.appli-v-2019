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
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
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
     * List of all the persons
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/person/list",
     *    name="person_list",
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     description="Number of the page",
     *     type="integer",
     * )
     * @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="Number of records",
     *     type="integer",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=Person::class)
     * )
     */
    public function listAll(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('personList');

        $persons = $paginator->paginate(
            $this->personService->getAllInArray(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        return new JsonResponse($persons->getItems());
    }

//SEARCH
    /**
     * Search within database "/person/search/{term}"
     * Optionnal: size(int) Number of records
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/person/search/{term}",
     *    name="person_search",
     *    requirements={"term": "^([a-zA-Z]+)"},
     *    methods={"HEAD", "GET"})
     */
    public function search(Request $request, string $term)
    {
        $this->denyAccessUnlessGranted('personSearch');

        $searchData = $this->personService->search($term, $request->query->getInt('size', 50));

        return new JsonResponse($searchData);
    }

//CREATE
    /**
     * Creates a person "/person/create"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/person/create",
     *    name="person_create",
     *    methods={"HEAD", "POST"})
     */
    public function create(Request $request)
    {
        $person = new Person();
        $this->denyAccessUnlessGranted('personCreate', $person);

        $createdData = $this->personService->create($person, $request->request);

        return new JsonResponse($createdData);
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
     * @Entity("person", expr="repository.findOneById(id)")
     */
    public function display(Person $person)
    {
        $this->denyAccessUnlessGranted('personDisplay', $person);

        $personArray = $this->personService->filter($person->toArray());

        return new JsonResponse($personArray);
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
     * @Entity("person", expr="repository.findOneById(id)")
     */
    public function modify(Request $request, Person $person)
    {
        $this->denyAccessUnlessGranted('personModify', $person);

        $modifiedData = $this->personService->modify($person, $request->request);

        return new JsonResponse($modifiedData);
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
     * @Entity("person", expr="repository.findOneById(id)")
     */
    public function delete(Person $person)
    {
        $this->denyAccessUnlessGranted('personDelete', $person);

        $suppressedData = $this->personService->delete($person);

        return new JsonResponse($suppressedData);
    }
}

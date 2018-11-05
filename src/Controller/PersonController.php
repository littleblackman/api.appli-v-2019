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
use App\Service\PersonServiceInterface;
use App\Entity\Person;
use App\Form\PersonType;

/**
 * PersonController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PersonController extends AbstractController
{
    private $personService;

    public function __construct(PersonServiceInterface $personService)
    {
        $this->personService = $personService;
    }

//LIST
    /**
     * Lists all the persons
     *
     * @Route("/person/list",
     *    name="person_list",
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="personId", type="integer"),
     *         @SWG\Property(property="firstname", type="string"),
     *         @SWG\Property(property="lastname", type="string"),
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
     * )
     * @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="Number of records",
     *     type="integer",
     * )
     * @SWG\Tag(name="Person")
     */
    public function listAll(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('personList');

        $persons = $paginator->paginate(
            $this->personService->findAllInArray(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        return new JsonResponse($persons->getItems());
    }

//SEARCH
    /**
     * Searches for %{term}% in firstname|lastname for Person
     *
     * @Route("/person/search/{term}",
     *    name="person_search",
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
     * )
     * @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="Number of records",
     *     type="integer",
     * )
     * @SWG\Tag(name="Person")
     */
    public function search(Request $request, PaginatorInterface $paginator, string $term)
    {
        $this->denyAccessUnlessGranted('personSearch');

        $persons = $paginator->paginate(
            $this->personService->findAllInSearch($term),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        return new JsonResponse($persons->getItems());
    }

//CREATE
    /**
     * Creates a Person
     *
     * @Route("/person/create",
     *    name="person_create",
     *    methods={"HEAD", "POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="person", @Model(type=Person::class)),
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Person",
     *     required=true,
     *     @Model(type=PersonType::class)
     * )
     * @SWG\Tag(name="Person")
     */
    public function create(Request $request)
    {
        $person = new Person();
        $this->denyAccessUnlessGranted('personCreate', $person);

        $createdData = $this->personService->create($person, $request->getContent());

        return new JsonResponse($createdData);
    }

//DISPLAY
    /**
     * Displays person
     *
     * @Route("/person/display/{personId}",
     *    name="person_display",
     *    requirements={"personId": "^([0-9]+)"},
     *    methods={"HEAD", "GET"})
     * @Entity("person", expr="repository.findOneById(personId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Person::class))
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
     *     name="personId",
     *     in="path",
     *     required=true,
     *     description="Id of the person",
     *     type="integer",
     * )
     * @SWG\Tag(name="Person")
     */
    public function display(Person $person)
    {
        $this->denyAccessUnlessGranted('personDisplay', $person);

        $personArray = $this->personService->filter($person->toArray());

        return new JsonResponse($personArray);
    }

//MODIFY
    /**
     * Modifies person
     *
     * @Route("/person/modify/{personId}",
     *    name="person_modify",
     *    requirements={"personId": "^([0-9]+)"},
     *    methods={"HEAD", "PUT"})
     * @Entity("person", expr="repository.findOneById(personId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="person", @Model(type=Person::class)),
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
     *     name="personId",
     *     in="path",
     *     required=true,
     *     description="Id of the person",
     *     type="integer",
     * )
     * @SWG\Tag(name="Person")
     */
    public function modify(Request $request, Person $person)
    {
        $this->denyAccessUnlessGranted('personModify', $person);

        $modifiedData = $this->personService->modify($person, $request->getContent());

        return new JsonResponse($modifiedData);
    }

//DELETE
    /**
     * Deletes person
     *
     * @Route("/person/delete/{personId}",
     *    name="person_delete",
     *    requirements={"personId": "^([0-9]+)"},
     *    methods={"HEAD", "DELETE"})
     * @Entity("person", expr="repository.findOneById(personId)")
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
     *     name="personId",
     *     in="path",
     *     required=true,
     *     description="Id of the person",
     *     type="integer",
     * )
     * @SWG\Tag(name="Person")
     */
    public function delete(Person $person)
    {
        $this->denyAccessUnlessGranted('personDelete', $person);

        $suppressedData = $this->personService->delete($person);

        return new JsonResponse($suppressedData);
    }
}

<?php

namespace App\Controller;

use App\Entity\Person;
use App\Form\PersonType;
use App\Service\PersonServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Person::class))
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
     * @SWG\Tag(name="Person")
     */
    public function listAll(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('personList');

        $persons = $paginator->paginate(
            $this->personService->findAll(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $personsArray = array();
        foreach ($persons->getItems() as $person) {
            $personsArray[] = $this->personService->toArray($person);
        };

        return new JsonResponse($personsArray);
    }

//SEARCH
    /**
     * Searches for %{term}% in firstname|lastname for Person
     *
     * @Route("/person/search/{term}",
     *    name="person_search",
     *    requirements={"term": "^([a-zA-Z0-9\ \-]+)"},
     *    methods={"HEAD", "GET"})
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
     * @SWG\Tag(name="Person")
     */
    public function search(Request $request, PaginatorInterface $paginator, string $term)
    {
        $this->denyAccessUnlessGranted('personList');

        $persons = $paginator->paginate(
            $this->personService->findAllSearch($term),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $personsArray = array();
        foreach ($persons->getItems() as $person) {
            $personsArray[] = $this->personService->toArray($person);
        };

        return new JsonResponse($personsArray);
    }

//DISPLAY WITH ID
    /**
     * Displays person using its id
     *
     * @Route("/person/display/{personId}",
     *    name="person_display",
     *    requirements={"personId": "^([0-9]+)$"},
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

        $personArray = $this->personService->toArray($person);

        return new JsonResponse($personArray);
    }

//DISPLAY WITH USER'S IDENTIFIER
    /**
     * Displays person using its user's identifier
     *
     * @Route("/person/display/{identifier}",
     *    name="person_display_identifier",
     *    requirements={"identifier": "^([a-z0-9]{32})"},
     *    methods={"HEAD", "GET"})
     * @Entity("person", expr="repository.findByUserIdentifier(identifier)")
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
     *     name="identifier",
     *     in="path",
     *     required=true,
     *     description="User's identifier of the person",
     *     type="string",
     * )
     * @SWG\Tag(name="Person")
     */
    public function displayByIdentifier(Person $person)
    {
        $this->denyAccessUnlessGranted('personDisplay', $person);

        $personArray = $this->personService->toArray($person);

        return new JsonResponse($personArray);
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
     *         @SWG\Property(property="person", ref=@Model(type=Person::class)),
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
        $this->denyAccessUnlessGranted('personCreate');

        $createdData = $this->personService->create($request->getContent());

        return new JsonResponse($createdData);
    }

//MODIFY
    /**
     * Modifies person
     *
     * @Route("/person/modify/{personId}",
     *    name="person_modify",
     *    requirements={"personId": "^([0-9]+)$"},
     *    methods={"HEAD", "PUT"})
     * @Entity("person", expr="repository.findOneById(personId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="person", ref=@Model(type=Person::class)),
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
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Person",
     *     required=true,
     *     @Model(type=PersonType::class)
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
     *    requirements={"personId": "^([0-9]+)$"},
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

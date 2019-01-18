<?php

namespace App\Controller;

use App\Entity\Registration;
use App\Form\RegistrationType;
use App\Service\RegistrationServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * RegistrationController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class RegistrationController extends AbstractController
{
    private $registrationService;

    public function __construct(RegistrationServiceInterface $registrationService)
    {
        $this->registrationService = $registrationService;
    }

//LIST

    /**
     * Lists all the registrations
     *
     * @Route("/registration/list",
     *    name="registration_list",
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Registration::class))
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
     * @SWG\Tag(name="Registration")
     */
    public function listAll(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('registrationList');

        $registrations = $paginator->paginate(
            $this->registrationService->findAll(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $registrationsArray = array();
        foreach ($registrations->getItems() as $registration) {
            $registrationsArray[] = $this->registrationService->toArray($registration);
        };

        return new JsonResponse($registrationsArray);
    }

//DISPLAY

    /**
     * Displays registration
     *
     * @Route("/registration/display/{registrationId}",
     *    name="registration_display",
     *    requirements={"registrationId": "^([0-9]+)"},
     *    methods={"HEAD", "GET"})
     * @Entity("registration", expr="repository.findOneById(registrationId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=Registration::class),
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
     *     description="Id of the registration",
     *     type="integer",
     * )
     * @SWG\Tag(name="Registration")
     */
    public function display(Request $request, Registration $registration)
    {
        $this->denyAccessUnlessGranted('registrationDisplay', $registration);

        $registrationArray = $this->registrationService->toArray($registration);

        return new JsonResponse($registrationArray);
    }

//CREATE

    /**
     * Creates a registration
     *
     * @Route("/registration/create",
     *    name="registration_create",
     *    methods={"HEAD", "POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="registration", ref=@Model(type=Registration::class)),
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Registration",
     *     required=true,
     *     @Model(type=RegistrationType::class)
     * )
     * @SWG\Tag(name="Registration")
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('registrationCreate', null);

        $createdData = $this->registrationService->create($request->getContent());

        return new JsonResponse($createdData);
    }

//MODIFY

    /**
     * Modifies registration
     *
     * @Route("/registration/modify/{registrationId}",
     *    name="registration_modify",
     *    requirements={"registrationId": "^([0-9]+)"},
     *    methods={"HEAD", "PUT"})
     * @Entity("registration", expr="repository.findOneById(registrationId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="registration", ref=@Model(type=Registration::class)),
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
     *     description="Id of the registration",
     *     type="integer",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Registration",
     *     required=true,
     *     @Model(type=RegistrationType::class)
     * )
     * @SWG\Tag(name="Registration")
     */
    public function modify(Request $request, Registration $registration)
    {
        $this->denyAccessUnlessGranted('registrationModify', $registration);

        $modifiedData = $this->registrationService->modify($registration, $request->getContent());

        return new JsonResponse($modifiedData);
    }

//DELETE

    /**
     * Deletes registration
     *
     * @Route("/registration/delete/{registrationId}",
     *    name="registration_delete",
     *    requirements={"registrationId": "^([0-9]+)"},
     *    methods={"HEAD", "DELETE"})
     * @Entity("registration", expr="repository.findOneById(registrationId)")
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
     *     description="Id of the registration",
     *     type="integer",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Registration",
     *     required=true,
     *     @Model(type=RegistrationType::class)
     * )
     * @SWG\Tag(name="Registration")
     */
    public function delete(Registration $registration)
    {
        $this->denyAccessUnlessGranted('registrationDelete', $registration);

        $suppressedData = $this->registrationService->delete($registration);

        return new JsonResponse($suppressedData);
    }
}

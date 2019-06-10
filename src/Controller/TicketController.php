<?php

namespace App\Controller;

use App\Entity\Ticket;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Service\TicketServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TicketController class
 * @author Sandy Razafitrimo <sandyrazafitrimo@gmail.com>
 */
class TicketController extends AbstractController
{
    private $ticketService;

    public function __construct(TicketServiceInterface $ticketService)
    {
        $this->ticketService = $ticketService;
    }

//LIST ALL
    /**
     * List all the ticket
     *
     * @Route("/ticket/list",
     *    name="ticket_list",
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Ticket::class))
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
     * @SWG\Tag(name="Ticket")
     */
    public function list(Request $request, PaginatorInterface $paginator)
    {
        //$this->denyAccessUnlessGranted('registrationList');

        $ticketsArray = array();

        /*
        $ticketsArray = $paginator->paginate(
            $this->ticketService->findAll(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );*/

        $ticketsArray = $this->ticketService->findAll();

        /*
        foreach ($registrations->getItems() as $registration) {
            $registrationsArray[] = $this->registrationService->toArray($registration);
        };*/

        return new JsonResponse($ticketsArray);
    }

    //CREATE
        /**
         * Create a ticket
         *
         * @Route("/ticket/create",
         *    name="ticket_create",
         *    methods={"HEAD", "POST"})
         *
         * @SWG\Response(
         *     response=200,
         *     description="Success",
         *     @SWG\Schema(
         *         @SWG\Property(property="status", type="boolean"),
         *         @SWG\Property(property="message", type="string"),
         *         @SWG\Property(property="ticket", ref=@Model(type=Ticket::class)),
         *     )
         * )
         * @SWG\Response(
         *     response=403,
         *     description="Access denied",
         * )
         * @SWG\Tag(name="Ticket")
         */
        public function create(Request $request)
        {
            //$this->denyAccessUnlessGranted('registrationCreate');
            $createdData = $this->ticketService->create($request->getContent());
            return new JsonResponse($createdData);
        }


    //DISLAY
        /**
         * List all the ticket
         *
         * @Route("/ticket/display/{id}",
         *    name="television_delete",
         *    requirements={"id": "^([0-9]+)$"},
         *    methods={"HEAD", "GET"})
         * @Entity("component", expr="repository.find(id)")
         *
         * @SWG\Response(
         *     response=200,
         *     description="Success",
         *     @SWG\Schema(
         *         type="array",
         *         @SWG\Items(ref=@Model(type=Ticket::class))
         *     )
         * )
         * @SWG\Response(
         *     response=403,
         *     description="Access denied",
         * )
         * @SWG\Tag(name="Ticket")
         */
        public function display(Ticket $ticket)
        {

            $ticketsArray = $ticket->toArray();


            return new JsonResponse($ticketsArray);
        }






}

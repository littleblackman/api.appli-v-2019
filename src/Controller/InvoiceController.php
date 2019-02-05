<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Form\InvoiceType;
use App\Service\InvoiceServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * InvoiceController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class InvoiceController extends AbstractController
{
    private $invoiceService;

    public function __construct(InvoiceServiceInterface $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

//LIST

    /**
     * Lists all the invoices
     *
     * @Route("/invoice/list",
     *    name="invoice_list",
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Invoice::class))
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
     * @SWG\Tag(name="Invoice")
     */
    public function listAll(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('invoiceList');

        $invoices = $paginator->paginate(
            $this->invoiceService->findAll(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $invoicesArray = array();
        foreach ($invoices->getItems() as $invoice) {
            $invoicesArray[] = $this->invoiceService->toArray($invoice);
        };

        return new JsonResponse($invoicesArray);
    }

//SEARCH

    /**
     * Searches for %{term}% in name_fr for Invoice
     *
     * @Route("/invoice/search/{term}",
     *    name="invoice_search",
     *    requirements={"term": "^([a-zA-Z0-9\ \-]+)"},
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Invoice::class))
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
     * @SWG\Tag(name="Invoice")
     */
    public function search(Request $request, PaginatorInterface $paginator, string $term)
    {
        $this->denyAccessUnlessGranted('invoiceList');

        $invoices = $paginator->paginate(
            $this->invoiceService->findAllSearch($term),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $invoicesArray = array();
        foreach ($invoices->getItems() as $invoice) {
            $invoicesArray[] = $this->invoiceService->toArray($invoice);
        };

        return new JsonResponse($invoicesArray);
    }

//DISPLAY

    /**
     * Displays invoice
     *
     * @Route("/invoice/display/{invoiceId}",
     *    name="invoice_display",
     *    requirements={"invoiceId": "^([0-9]+)$"},
     *    methods={"HEAD", "GET"})
     * @Entity("invoice", expr="repository.findOneById(invoiceId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=Invoice::class)
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
     *     name="invoiceId",
     *     in="path",
     *     description="Id of the invoice",
     *     type="integer",
     * )
     * @SWG\Tag(name="Invoice")
     */
    public function display(Invoice $invoice)
    {
        $this->denyAccessUnlessGranted('invoiceDisplay', $invoice);

        $invoiceArray = $this->invoiceService->toArray($invoice);

        return new JsonResponse($invoiceArray);
    }

//CREATE

    /**
     * Creates invoice
     *
     * @Route("/invoice/create",
     *    name="invoice_create",
     *    methods={"HEAD", "POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="invoice", ref=@Model(type=Invoice::class)),
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Invoice",
     *     required=true,
     *     @Model(type=InvoiceType::class)
     * )
     * @SWG\Tag(name="Invoice")
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('invoiceCreate', null);

        $createdData = $this->invoiceService->create($request->getContent());

        return new JsonResponse($createdData);
    }

//MODIFY

    /**
     * Modifies invoice
     *
     * @Route("/invoice/modify/{invoiceId}",
     *    name="invoice_modify",
     *    requirements={"invoiceId": "^([0-9]+)$"},
     *    methods={"HEAD", "PUT"})
     * @Entity("invoice", expr="repository.findOneById(invoiceId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="invoice", ref=@Model(type=Invoice::class)),
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
     *     name="invoiceId",
     *     in="path",
     *     description="Id for the invoice",
     *     required=true,
     *     type="integer",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Invoice",
     *     required=true,
     *     @Model(type=InvoiceType::class)
     * )
     * @SWG\Tag(name="Invoice")
     */
    public function modify(Request $request, Invoice $invoice)
    {
        $this->denyAccessUnlessGranted('invoiceModify', $invoice);

        $modifiedData = $this->invoiceService->modify($invoice, $request->getContent());

        return new JsonResponse($modifiedData);
    }

//DELETE

    /**
     * Deletes invoice
     *
     * @Route("/invoice/delete/{invoiceId}",
     *    name="invoice_delete",
     *    requirements={"invoiceId": "^([0-9]+)$"},
     *    methods={"HEAD", "DELETE"})
     * @Entity("invoice", expr="repository.findOneById(invoiceId)")
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
     *     name="invoiceId",
     *     in="path",
     *     description="Id for the invoice",
     *     required=true,
     *     type="integer",
     * )
     * @SWG\Tag(name="Invoice")
     */
    public function delete(Invoice $invoice)
    {
        $this->denyAccessUnlessGranted('invoiceDelete', $invoice);

        $suppressedData = $this->invoiceService->delete($invoice);

        return new JsonResponse($suppressedData);
    }
}

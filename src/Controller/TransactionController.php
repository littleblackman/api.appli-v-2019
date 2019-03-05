<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Service\TransactionServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TransactionController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class TransactionController extends AbstractController
{
    private $transactionService;

    public function __construct(TransactionServiceInterface $transactionService)
    {
        $this->transactionService = $transactionService;
    }

//LIST

    /**
     * Lists all the transaction
     *
     * @Route("/transaction/list",
     *    name="transaction_list",
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Transaction::class))
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
     * @SWG\Tag(name="Transaction")
     */
    public function listAll(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('transactionList');

        $transactions = $paginator->paginate(
            $this->transactionService->findAll(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $transactionsArray = array();
        foreach ($transactions->getItems() as $transaction) {
            $transactionsArray[] = $this->transactionService->toArray($transaction);
        };

        return new JsonResponse($transactionsArray);
    }

//DISPLAY

    /**
     * Displays transaction using transactionId
     *
     * @Route("/transaction/display/{transactionId}",
     *    name="transaction_display",
     *    requirements={"transactionId": "^([0-9]+)$"},
     *    methods={"HEAD", "GET"})
     * @Entity("transaction", expr="repository.findOneById(transactionId)")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Transaction::class))
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
     *     name="transactionId",
     *     in="path",
     *     required=true,
     *     description="Id of the transaction",
     *     type="integer",
     * )
     * @SWG\Tag(name="Transaction")
     */
    public function display(Transaction $transaction)
    {
        $this->denyAccessUnlessGranted('transactionDisplay', $transaction);

        $transactionArray = $this->transactionService->toArray($transaction);

        return new JsonResponse($transactionArray);
    }

//CREATE

    /**
     * Creates Transaction
     *
     * @Route("/transaction/create",
     *    name="transaction_create",
     *    methods={"HEAD", "POST"})
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
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Data for the Transaction",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=TransactionType::class))
     *     )
     * )
     * @SWG\Tag(name="Transaction")
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('transactionCreate', null);

        $createdData = $this->transactionService->create($request->getContent());

        return new JsonResponse($createdData);
    }

//DELETE

    /**
     * Deletes transaction using its id
     *
     * @Route("/transaction/delete/{transactionId}",
     *    name="transaction_delete",
     *    requirements={"transactionId": "^([0-9]+)$"},
     *    methods={"HEAD", "DELETE"})
     * @Entity("component", expr="repository.findOneById(transactionId)")
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
     *     name="transactionId",
     *     in="path",
     *     description="Id for the Transaction",
     *     required=true,
     *     type="integer",
     * )
     * @SWG\Tag(name="Transaction")
     */
    public function delete(Transaction $transaction)
    {
        $this->denyAccessUnlessGranted('transactionDelete', $transaction);

        $suppressedData = $this->transactionService->delete($transaction);

        return new JsonResponse($suppressedData);
    }
}

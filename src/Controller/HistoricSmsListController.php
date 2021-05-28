<?php

namespace App\Controller;

use App\Entity\HistoricSmsList;
use App\Service\HistoricSmsListServiceInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ExtractListController class
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class HistoricSmsListController extends AbstractController
{
    private $historicSmsListService;

    public function __construct(HistoricSmsListServiceInterface $historicSmsListService)
    {
        $this->historicSmsListService = $historicSmsListService;
    }


//MODIFY
    /**
     * Modifies historicSms
     *
     * @Route("/historicSmsList/addNumberToList",
     *    name="historicSmsList_addNumberToList",
     *    requirements={"id": "^([0-9]+)$"},
     *    methods={"HEAD", "POST"})
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="extractList", ref=@Model(type=HistoricSmsList::class)),
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
     * @SWG\Tag(name="HistoricSmsList")
     */
    public function addNumberToList(Request $request) {
    
        $result = $this->historicSmsListService->addNumberToList($request->getContent());
        return new JsonResponse($result);
    }

    //MODIFY
    /**
     * Modifies historicSms
     *
     * @Route("/historicSmsList/updateDoSend",
     *    name="historicSmsList_updateDoSend",
     *    methods={"HEAD", "POST"})
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         @SWG\Property(property="status", type="boolean"),
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="extractList", ref=@Model(type=HistoricSmsList::class)),
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
     * @SWG\Tag(name="HistoricSmsList")
     */
    public function updateDoSend(Request $request) {
    
        $result = $this->historicSmsListService->updateDoSend($request->getContent());
        return new JsonResponse($result);
    }

}

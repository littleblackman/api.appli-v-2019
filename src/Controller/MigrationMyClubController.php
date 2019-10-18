<?php

namespace App\Controller;

use App\Entity\Standard;
use App\Service\MyClub\MigrationMyClubService;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\StandardServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MigrationMyClubController class
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class MigrationMyClubController extends AbstractController
{
    private $migrationMyClubService;
    private $em;

    public function __construct(EntityManagerInterface $em, MigrationMyClubService $migrationMyClubService)
    {
        $this->em = $em;
        $this->migrationMyClubService = $migrationMyClubService;
    }

//TRANSPORTS BY DATE
    /**
     * Retrieve transport from my club from a date
     *
     * @Route("/migration/retrieve/transport/{date}",
     *    name="retrieve_transport_dy_date",
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Standard::class))
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
     * @SWG\Tag(name="MigrationMyClub")
     */
    public function getTransportByDate($date)
    {
        $data = $this->migrationMyClubService->getTransportByDate($date);

        return new JsonResponse($data);
    }


//ACTIVITY BY DATE
    /**
     * Retrieve activity from my club from a date
     *
     * @Route("/migration/retrieve/activity/{date}",
     *    name="retrieve_activity_dy_date",
     *    methods={"HEAD", "GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Standard::class))
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
     * @SWG\Tag(name="MigrationMyClub")
     */
    public function getActivityByDate($date)
    {
        $data = $this->migrationMyClubService->getActivityByDate($date);

        return new JsonResponse($data);
    }

}

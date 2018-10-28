<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
//HOME
    /**
     * Home of the API
     * @return JsonResponse (true)
     *
     * @Route("/",
     *    name="home",
     *    methods={"HEAD", "GET"})
     */
    public function home()
    {
        return new JsonResponse(true);
    }
}

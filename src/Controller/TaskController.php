<?php

namespace App\Controller;

use App\Entity\Task;

use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


/**
 * Task class
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class TaskController extends AbstractController
{
  //LIST
      /**
       * Lists all the task
       *
       * @Route("/task/list",
       *    name="task_list",
       *    methods={"HEAD", "GET"})
       *
       * @SWG\Response(
       *     response=200,
       *     description="Success",
       *     @SWG\Schema(
       *         type="array",
       *         @SWG\Items(ref=@Model(type=Task::class))
       *     )
       * )
       * @SWG\Response(
       *     response=403,
       *     description="Access denied",
       * )
       * @SWG\Tag(name="Task")
       */
      public function list(Request $request, EntityManagerInterface $em)
      {
          //$this->denyAccessUnlessGranted('taskList');
          $tasks = $em->getRepository('App:Task')->findAll();

          $arr = [];

         foreach($tasks as $task) {
           $arr[$task->getMoment()][$task->getId()] = $task->getName();   
         }
          return new JsonResponse($arr);
    }

}

<?php

namespace App\Service;

use App\Entity\TaskStaff;
use DateTime;
use App\Service\TaskStaffServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
* TaskStaffService class
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class TaskStaffService implements TaskStaffServiceInterface
{
    private $em;


    private $mainService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService
    )
    {
        $this->em = $em;
        $this->taskService = $taskService;
        $this->mainService = $mainService;
    }


    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        $data = json_decode($data, true);
        if (is_array($data) && !empty($data)) {



            //Returns data
            return array(
                'status' => true,
                'message' => 'TaskStaff ajoutée',
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . json_encode($data));
    }


}

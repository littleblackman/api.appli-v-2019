<?php

namespace App\Service;

use App\Entity\TaskStaff;
use App\Entity\Staff;
use DateTime;

use App\Service\StaffServiceInterface;
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
        StaffServiceInterface $staffService,
        MainServiceInterface $mainService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
        $this->staffService = $staffService;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyStep(string $data)
    {
        $values = json_decode($data, true);

        $task_staff_id = $values['task_staff_id'];
        $step          = $values['step'];

        $taskStaff = $this->em->getRepository('App:TaskStaff')->find($task_staff_id);
        $taskStaff->setStep(mb_strtoupper($step));

        $this->mainService->create($taskStaff);

        //Persists data
        $this->mainService->persist($taskStaff);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Tache modifiée' ,
            'task_staff_id' => $taskStaff->getId()
        );



    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        $values = json_decode($data, true);

        $date = $values['date_task'];

        isset($values['task_id'])        ?  $task_id = $values['task_id'] : $task_id = null;
        isset($values['remote_address']) ?  $remoteAddress = $values['remote_address'] : $remoteAddress = null;
        isset($values['staff_id'])       ?  $staff_id = $values['staff_id'] : $staff_id = null;
        isset($values['supervisor_id'])  ?  $supervisor_id = $values['supervisor_id'] : $supervisor_id = null;

        if (is_array($values) && !empty($values)) {

            ($staff_id) ? $staff = $this->em->getRepository('App:Staff')->find($staff_id) : $staff = null;
            ($task_id) ? $task  = $this->em->getRepository('App:Task')->find($task_id) : $task = null;
            ($supervisor_id) ? $supervisor  = $this->em->getRepository('App:Staff')->find($supervisor_id) : $supervisor = null;

            ($values['name'] == null && $task != null) ? $name = $task->getName() : $name = $values['name'];

            $dateTask = new DateTime($date);

            $object = new TaskStaff();
            $object->setName($name);
            $object->setDescription($values['description']);
            $object->setSupervisor($supervisor);
            $object->setStep($values['step']);
            $object->setTask($task);
            $object->setStaff($staff);
            $object->setDateTask($dateTask);
            $object->setRemoteAddress($remoteAddress);

            $this->mainService->create($object);

            //Persists data
            $this->mainService->persist($object);


            if($supervisor != null) {
                $object2 = new TaskStaff();
                $object2->setName('SUPERVISION: '.$staff->getPerson()->getFirstname().' '.$staff->getPerson()->getLastname().' - '.$name);
                $object2->setDescription($values['description']);
                $object2->setSupervisor(null);
                $object2->setStep($values['step']);
                $object2->setTask($task);
                $object2->setStaff($supervisor);
                $object2->setDateTask($dateTask);
                $object2->setRemoteAddress($remoteAddress);
            }

            $this->mainService->create($object2);

            //Persists data
            $this->mainService->persist($object2);


            //Returns data
            return array(
                'status' => true,
                'message' => 'Tache ajoutée' ,
                'task_staff_id' => $object->getId()
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . json_encode($data));
    }



    /**
     * {@inheritdoc}
     */
    public function update(string $data)
    {
        $values = json_decode($data, true);

        $object = $this->em->getRepository('App:TaskStaff')->find($values['id']);
        isset($values['supervisor_id']) ?  $supervisor_id = $values['supervisor_id'] : $supervisor_id = null;
        ($supervisor_id) ? $supervisor  = $this->em->getRepository('App:Staff')->find($supervisor_id) : $supervisor = null;
        isset($values['staff_id']) ?  $staff_id = $values['staff_id'] : $staff_id = null;
        ($staff_id) ? $staff = $this->em->getRepository('App:Staff')->find($staff_id) : $staff = null;

        $object->setName($values['name']);
        $object->setDescription($values['description']);
        $object->setSupervisor($supervisor);
        $object->setStep($values['step']);
        $object->setTask(null);
        $object->setStaff($staff);
        $object->setDateTask( new DateTime($values['date_task']));
        $object->setRemoteAddress($values['remote_address']);


        $this->mainService->modify($object);

        //Persists data
        $this->mainService->persist($object);


        //Returns data
        return array(
            'status' => true,
            'message' => 'Tache ajoutée' ,
            'task_staff_id' => $object->getId()
        );


    }





    public function delete(string $data)
    {
        $values = json_decode($data, true);

        $taskStaffId = $values['taskStaffId'];
        $taskStaff   = $this->em->getRepository('App:TaskStaff')->find($taskStaffId);

        $this->em->remove($taskStaff);
        $this->em->flush();

        return array(
            'status' => true,
            'message' => 'Tache supprimée' ,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveTasks($staffId, $date)
    {

        $staff = $this->em->getRepository('App:Staff')->find($staffId);
        $tasks = $this->em->getRepository('App:TaskStaff')->findByStaffAndDate($staff, $date);
        return $tasks;
    }

    /**
     * {@inheritdoc}
     */
    public function list($date)
    {
        $tasks = $this->em->getRepository('App:TaskStaff')->findByDate($date);
        return $tasks;
    }

    /**
     * {@inheritdoc}
     */
    public function listByStep($step, $staffId = null)
    {

        if($staffId) {
            if(!$staff = $this->em->getRepository('App:Staff')->find($staffId)) {
                return array('message' => 'Staff person not founded');
            }
        } else {
          $staff = null;
        }
        $taskStaffs = $this->em->getRepository('App:TaskStaff')->findByStep($step, $staff);
        $taskStaffsArray = array();
        foreach ($taskStaffs as $taskStaff) {
            $taskStaffsArray[] = $this->toArray($taskStaff);
        };
        return $taskStaffsArray;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(TaskStaff $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related staff
        if (null !== $object->getSupervisor()) {
            $objectArray['supervisor'] = $this->staffService->toArray($object->getSupervisor());
        }


        //Gets related staff
        if (null !== $object->getStaff() && !$object->getStaff()->getSuppressed()) {
            $objectArray['staff'] = $this->staffService->toArray($object->getStaff());
        }
        //Gets related task
        if (null !== $object->getTask()) {
            $objectArray['task'] = $object->getTask()->toArray();
        }
        return $objectArray;
    }

}

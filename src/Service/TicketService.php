<?php

namespace App\Service;

use App\Entity\Ticket;
use App\Entity\Staff;
use App\Entity\Category;
use App\Entity\Location;
use App\Entity\TaskStaff;
use App\Entity\Rdv;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * TicketService class
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class TicketService implements TicketServiceInterface
{
    private $em;

    private $mainService;
    private $categoryService;
    private $staffService;


    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService,
        CategoryServiceInterface $categoryService,
        StaffServiceInterface $staffService

    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
        $this->categoryService = $categoryService;
        $this->staffService = $staffService;

    }

    /**
     * Returns the list of all TICKET
     */
    public function findAll()
    {

        $tickets = $this->em
            ->getRepository('App:Ticket')
            ->findBy(array(), array('dateCall' => 'DESC'));
        ;

        $array = [];
        foreach($tickets as $ticket)
        {
            $array[] = $this->toArray($ticket);
        }

        return $array;

    }


    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        $values = json_decode($data, true);

        if(!$staff = $this->em->getRepository('App:Staff')->find($values['staff_id'])) $staff = null;
        if(!$category  = $this->em->getRepository('App:Category')->find($values['category_id'])) $category = null;
        if(!$location = $this->em->getRepository('App:Location')->find($values['location_id'])) $location = null;
        if(!$taskStaff = $this->em->getRepository('App:TaskStaff')->find($values['taskStaff_id'])) $taskStaff = null;
        if(!$rdv = $this->em->getRepository('App:Rdv')->find($values['rdv_id'])) $rdv = null;

        $dateCall = new DateTime($values['date_call']);

        //Submits data

        if (is_array($values) && !empty($values)) {
            $object = new Ticket();
            $object->setStaff($staff);
            $object->setName($values['name']);
            $object->setTel($values['tel']);
            $object->setContent($values['content']);
            $object->setCategory($category);
            $object->setLocation($location);
            $object->setRdv($rdv);
            $object->setPersona($values['persona']);
            $object->setTaskStaff($taskStaff);
            $object->setRecall($values['recall']);
            $object->setType($values['type']);
            $object->setDateCall($dateCall);
            $object->setOriginCall($values['origin_call']);

            $this->mainService->create($object);

            //Persists data
            $this->mainService->persist($object);

            // update rdv with location information
            if($rdv) {
                $rdv->setLocation($location);
                $this->em->persist($rdv);
                $this->em->flush();
            }

            //Returns data
            return array(
                'status' => true,
                'message' => 'Ticket ajouté',
                'ticket' => $object->toArray(),
            );
        }

        throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . json_encode($values));

    }



    /**
     * {@inheritdoc}
     */
    public function toArray(Ticket $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());


        //Gets related product
        if (null !== $object->getCategory()) {
            $objectArray['category'] = $this->categoryService->toArray($object->getCategory());
        }

        //Gets related staff
        if (null !== $object->getStaff()) {
            $objectArray['staff'] = $this->staffService->toArray($object->getStaff());
        }

        //Gets related location
        if (null !== $object->getLocation() && !$object->getLocation()->getSuppressed()) {
            $objectArray['location'] = $this->mainService->toArray($object->getLocation()->toArray());
        }


        return $objectArray;
    }
}

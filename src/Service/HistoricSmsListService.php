<?php

namespace App\Service;

use App\Entity\HistoricSms;
use App\Entity\Phone;
use App\Entity\HistoricSmsList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use \PDO;

use DateTime;


/**
 * ExtractListService class
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class HistoricSmsListService implements HistoricSmsListServiceInterface
{
    private $em;

    private $mainService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;

    }

    public function addNumberToList($data) {

        $dataArray = is_array($data) ? $data : json_decode($data, true);

        // retrieve historicSms
        if(!$historicSms = $this->em->getRepository('App:HistoricSms')->find($dataArray['historicSmsId'])) return ['message' => 'historicSms not founded'];

        // retrieve phone if exist
        if(!$phone = $this->em->getRepository('App:Phone')->findOneBy(['phone' => $dataArray['phoneNumber']])) $phone = null;

        // retrive child if exist
        if(!$child = $this->em->getRepository('App:Child')->find($dataArray['childId'])) $child = null;


        // check if phoneNumber exist in list
        if($isExist = $this->em->getRepository('App:HistoricSmsList')->findBy(['phoneNumber' => $dataArray['phoneNumber'], 'historicSms' => $historicSms])) return ['message' => 'number is alrealdy in list'];

        // persist data
        $historicSmsList = new HistoricSmsList();
        $historicSmsList->setPhoneName($dataArray['phoneName']);
        $historicSmsList->setPhoneNumber($dataArray['phoneNumber']);
        $historicSmsList->setHistoricSms($historicSms);
        $historicSmsList->setChild($child);
        $historicSmsList->setPhone($phone);

        $this->mainService->create($historicSmsList);
        $this->mainService->persist($historicSmsList);
        
        //Returns data
        return array(
            'status' => true,
            'message' => $dataArray['phoneNumber'].' add to historicSmsList',
            'data'    => $dataArray
        ); 

    }

    public function updateDoSend($data) {

        $dataArray = is_array($data) ? $data : json_decode($data, true);
        $historicSmsList = $this->em->getRepository('App:HistoricSmsList')->find($dataArray['historicSmsListId']);

        $moment = new DateTime(date('Y-m-d'));
        $historicSmsList->setDateSended($moment);
        $moment->setTime(date('H'), date('i'));
        $historicSmsList->setTimeSended($moment);

        $this->mainService->modify($historicSmsList);
        $this->mainService->persist($historicSmsList);

        //Returns data
        return array(
            'status' => true,
            'message' => 'HistoricSmsList modifiée',
            'HistoricSmsList' => $this->toArray($historicSmsList),
        );

    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new HistoricSmsList();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'historic-sms-list-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'HistoricSmsList ajoutée',
            'HistoricSmsList' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(HistoricSmsList $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'HistoricSmsList supprimée',
        );
    }

    /**
     * Returns the list of all families in the array format
     * @return array
     */
    public function findAll()
    {


        $lists = $this->em->getRepository('App:HistoricSmsList')->findAll();
        $result = [];
        foreach($lists as $list) {
            $result[] = $list->toArray();
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(HistoricSmsList $object)
    {
        if (
            null === $object->getContent()) {
            throw new UnprocessableEntityHttpException('Missing data for HistoricSmsList -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(HistoricSmsList $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'historic-sms-list-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'HistoricSmsList modifiée',
            'HistoricSmsList' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(HistoricSmsList $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        return $objectArray;
    }
}

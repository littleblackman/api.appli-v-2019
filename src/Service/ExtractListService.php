<?php

namespace App\Service;

use App\Entity\ExtractList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use \PDO;

/**
 * ExtractListService class
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class ExtractListService implements ExtractListServiceInterface
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

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new ExtractList();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'extract-list-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Liste ajoutée',
            'ExtractList' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ExtractList $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'Liste supprimée',
        );
    }

    /**
     * Returns the list of all families in the array format
     * @return array
     */
    public function findAll()
    {


        $lists = $this->em->getRepository('App:ExtractList')->findAll();
        $result = [];
        foreach($lists as $list) {
            $result[] = $list->toArray();
        }
        return $result;
    }


    /**
     *  Returns the result of executed request (mysq)
     *  @return array
     */
    public function listExecuteContent($extractList) {

        $query = $extractList->getContent();

        $conn = $this->em->getConnection();
        $r = $conn->prepare($query);
        $r->execute();
        $datas = $r->fetchAll(PDO::FETCH_ASSOC);

        $arr = [] ;

        foreach($datas as $data) {
            $phones[$data['child_id']][] = ['phone' => $data['phone_number'], 'name' => $data['phone_name']];
        }
        foreach($datas as $data) {
            $arr[trim($data['lastname'].' '.$data['firstname'])][] = [
                                                    'childId'         => $data['child_id'],
                                                    'fullnameReverse' => trim($data['lastname'].' '.$data['firstname']),
                                                    'registrationId'  => "",
                                                    'updatedAt'       => "",
                                                    'status'          => "",
                                                    'sessions'        => "",
                                                    'phones'          => $phones[$data['child_id']],
                                                    'personal'        => ""
            ];
        }
        ksort($arr);
        return $arr;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(ExtractList $object)
    {
        if (null === $object->getTitle() ||
            null === $object->getContent()) {
            throw new UnprocessableEntityHttpException('Missing data for ExtractList -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(ExtractList $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'extact-list-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Liste modifiée',
            'ExtractList' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(ExtractList $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        return $objectArray;
    }
}

<?php

namespace App\Service;

use App\Entity\Transaction;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * TransactionService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class TransactionService implements TransactionServiceInterface
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
        $object = new Transaction();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'transaction-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Transaction ajoutée',
            'transaction' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Transaction $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'Transaction supprimée',
        );
    }

    /**
     * Returns the list of all transaction
     * @return array
     */
    public function findAll()
    {
        return $this->em
            ->getRepository('App:Transaction')
            ->findAll()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Transaction $object)
    {
        if (null === $object->getInternalOrder() ||
            null === $object->getAmount() ||
            null === $object->getPerson()) {
            throw new UnprocessableEntityHttpException('Missing data for Transaction -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Transaction $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        return $objectArray;
    }
}

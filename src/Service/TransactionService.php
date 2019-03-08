<?php

namespace App\Service;

use App\Entity\Registration;
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
     * Adds specific data that could not be added via generic method
     */
    public function addSpecificData(Transaction $object, array $data)
    {
        //Adds links to Registration
        $this->addRegistrations($object, $data);
    }

    /**
     * Adds registrations links
     */
    public function addRegistrations(Transaction $object, array $data)
    {
        if (array_key_exists('registrations', $data)) {
            foreach ($data['registrations'] as $registrationData) {
                if (array_key_exists('registrationId', $registrationData)) {
                    $registration = $this->em->getRepository('App:Registration')->findOneByRegistrationId($registrationData['registrationId']);
                    if ($registration instanceof Registration) {
                        $registration->setTransaction($object);
                        $this->mainService->modify($registration);
                        $this->mainService->persist($registration);
                    }
                }
            }
        }
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
        $this->addSpecificData($object, $data);

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
        //Removes Registration links
        if (!$object->getRegistrations()->isEmpty()) {
            foreach ($object->getRegistrations() as $registration) {
                $registration->setTransaction(null);
                $this->mainService->modify($registration);
                $this->mainService->persist($registration);
            }
        }

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'Transaction supprimée',
        );
    }

    /**
     * Returns the list of all transactions
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
     * Returns the list of all transactions for a specific date
     * @return array
     */
    public function findAllByDate($date)
    {
        return $this->em
            ->getRepository('App:Transaction')
            ->findAllByDate($date)
        ;
    }

    /**
     * Returns the list of all transactions for a specific date and status
     * @return array
     */
    public function findAllByDateStatus($date, $status)
    {
        return $this->em
            ->getRepository('App:Transaction')
            ->findAllByDateStatus($date, $status)
        ;
    }

    /**
     * Returns the list of all transactions for a specific date and person
     * @return array
     */
    public function findAllByDatePerson($date, $person)
    {
        return $this->em
            ->getRepository('App:Transaction')
            ->findAllByDatePerson($date, $person)
        ;
    }

    /**
     * Returns the list of all transactions for a specific date and person
     * @return array
     */
    public function findAllByStatusPerson($status, $person)
    {
        return $this->em
            ->getRepository('App:Transaction')
            ->findAllByStatusPerson($status, $person)
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
    public function modify(Transaction $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'transaction-modify', $data);
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Transaction modifiée',
            'television' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Transaction $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related registrations
        if (null !== $object->getRegistrations()) {
            $registrations = array();
            foreach($object->getRegistrations() as $registration) {
                if (!$registration->getSuppressed()) {
                    $registrations[] = $this->mainService->toArray($registration->toArray());
                }
            }
            $objectArray['registrations'] = $registrations;
        }

        return $objectArray;
    }
}

<?php

namespace App\Service;

use App\Entity\Registration;
use App\Entity\ProductCancelledDate;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * ProductCancelledDateService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ProductCancelledDateService implements ProductCancelledDateServiceInterface
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
        $object = new ProductCancelledDate();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'product-cancelled-date-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'ProductCancelledDate ajoutée',
            'productCancelledDate' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ProductCancelledDate $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'ProductCancelledDate supprimée',
        );
    }

    /**
     * Returns the list of all productCancelledDates
     * @return array
     */
    public function findAll()
    {
        return $this->em
            ->getRepository('App:ProductCancelledDate')
            ->findAll()
        ;
    }

    /**
     * Returns the list of all productCancelledDates for a specific category and date
     * @return array
     */
    public function findAllByCategoryDate($categoryId, $date)
    {
        return $this->em
            ->getRepository('App:ProductCancelledDate')
            ->findAllByCategoryDate($categoryId, $date)
        ;
    }

    /**
     * Returns the list of all productCancelledDates for a specific date
     * @return array
     */
    public function findAllByDate($date)
    {
        return $this->em
            ->getRepository('App:ProductCancelledDate')
            ->findAllByDate($date)
        ;
    }

    /**
     * Returns the list of all productCancelledDates for a specific product and date
     * @return array
     */
    public function findAllByProductDate($productId, $date)
    {
        return $this->em
            ->getRepository('App:ProductCancelledDate')
            ->findAllByProductDate($productId, $date)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(ProductCancelledDate $object)
    {
        if (null === $object->getDate() ||
            null === $object->getProduct()) {
            throw new UnprocessableEntityHttpException('Missing data for ProductCancelledDate -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(ProductCancelledDate $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'product-cancelled-date-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'ProductCancelledDate modifiée',
            'productCancelledDate' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(ProductCancelledDate $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related category
        if (null !== $object->getCategory() && !$object->getCategory()->getSuppressed()) {
            $objectArray['category'] = $this->mainService->toArray($object->getCategory()->toArray());
        }

        //Gets related product
        if (null !== $object->getProduct() && !$object->getProduct()->getSuppressed()) {
            $objectArray['product'] = $this->mainService->toArray($object->getProduct()->toArray());
        }

        return $objectArray;
    }
}

<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\InvoiceComponent;
use App\Entity\InvoiceProduct;
use App\Service\TransactionServiceInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * InvoiceService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class InvoiceService implements InvoiceServiceInterface
{
    private $em;

    private $mainService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService,
        TransactionServiceInterface $transactionService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
        $this->transactionService = $transactionService;
    }

    /**
     * Adds link between Invoice and InvoiceProduct
     */
    public function addInvoiceProduct($data, Invoice $object)
    {
        //Submits data
        $invoiceProduct = new InvoiceProduct();
        $this->mainService->create($invoiceProduct);
        $this->mainService->submit($invoiceProduct, 'invoice-product-create', $data);
        $invoiceProduct->setInvoice($object);

        //Persists data
        $this->em->persist($invoiceProduct);

        if (array_key_exists('invoiceComponents', $data)) {
            foreach ($data['invoiceComponents'] as $invoiceComponent) {
                $this->addInvoiceComponent($invoiceComponent, $invoiceProduct);
            }
        }

    }

    /**
     * Adds link between InvoiceProduct and InvoiceComponent
     */
    public function addInvoiceComponent($data, InvoiceProduct $object)
    {
        //Submits data
        $invoiceComponent = new InvoiceComponent();
        $this->mainService->create($invoiceComponent);
        $this->mainService->submit($invoiceComponent, 'invoice-component-create', $data);
        $invoiceComponent->setInvoiceProduct($object);

        //Persists data
        $this->em->persist($invoiceComponent);
    }

    /**
     * Adds specific data that could not be added via generic method
     */
    public function addSpecificData(Invoice $object, array $data)
    {
        //Adds invoiceProducts + InvoiceComponents (via invoiceProducts)
        if (array_key_exists('invoiceProducts', $data)) {
            //Removes links to invoiceProducts
            $this->removeInvoiceProducts($object);

            //Adds submitted invoiceProducts + invoiceComponents
            $invoiceProducts = $data['invoiceProducts'];
            if (null !== $invoiceProducts && is_array($invoiceProducts) && !empty($invoiceProducts)) {
                foreach ($invoiceProducts as $invoiceProduct) {
                    $this->addInvoiceProduct($invoiceProduct, $object);
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
        $object = new Invoice();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'invoice-create', $data);
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Facture ajoutée',
            'invoice' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Invoice $object)
    {
        //Removes links to invoiceProducts + invoiceComponents
        $this->removeInvoiceProducts($object);

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'Facture supprimée',
        );
    }

    /**
     * Returns the list of all invoices in the array format
     * @return array
     */
    public function findAll()
    {
        return $this->em
            ->getRepository('App:Invoice')
            ->findAll()
        ;
    }

    /**
     * Searches the term in the Invoice collection
     * @return array
     */
    public function findAllSearch(string $term)
    {
        return $this->em
            ->getRepository('App:Invoice')
            ->findAllSearch($term)
        ;
    }

    /**
     * Searches the invoices between two dates in the Invoice collection
     * @return array
     */
    public function findAllSearchByDates(string $dateStart, string $dateEnd)
    {
        return $this->em
            ->getRepository('App:Invoice')
            ->findAllSearchByDates($dateStart, $dateEnd)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Invoice $object)
    {
        if (null === $object->getNameFr()) {
            throw new UnprocessableEntityHttpException('Missing data for Invoice -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Invoice $object, string $data)
    {

        $firstStatuts = $object->getStatus();

        //Submits data
        $data = $this->mainService->submit($object, 'invoice-modify', $data);
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);


        $firstStatuts = "test"; // delete for prod
        if($object->getStatus() != $firstStatuts) {
            if($transaction = $this->transactionService->findByInvoice($object)) {
                $transaction->setStatus($object->getStatus());
                $this->mainService->modify($transaction);
                $this->mainService->persist($transaction);

            }
        }

        //Returns data
        return array(
            'status' => true,
            'message' => 'Facture modifiée',
            'invoice' => $this->toArray($object),
        );
    }

    /**
     * Deletes all the invoiceProducts + invoiceComponents
     */
    public function removeInvoiceProducts(Invoice $object)
    {
        //Removes links to InvoiceProducts
        if (!$object->getInvoiceProducts()->isEmpty()) {
            foreach ($object->getInvoiceProducts() as $invoiceProduct) {
                //Removes links to invoiceComponents
                if (!$invoiceProduct->getInvoiceComponents()->isEmpty()) {
                    foreach ($invoiceProduct->getInvoiceComponents() as $invoiceComponent) {
                        $this->mainService->delete($invoiceComponent);
                        $this->em->persist($invoiceComponent);
                    }
                }
                $this->mainService->delete($invoiceProduct);
                $this->em->persist($invoiceProduct);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Invoice $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related child
        if (null !== $object->getChild() && !$object->getChild()->getSuppressed()) {
            $objectArray['child'] = $this->mainService->toArray($object->getChild()->toArray());
        }

        //Gets related invoiceProducts
        if (null !== $object->getInvoiceProducts()) {
            $invoiceProducts = array();
            $i = 0;
            foreach($object->getInvoiceProducts() as $invoiceProduct) {
                if (!$invoiceProduct->getSuppressed()) {
                    $invoiceProducts[$i] = $this->mainService->toArray($invoiceProduct->toArray());

                    //Gets related invoiceComponents
                    if (null !== $invoiceProduct->getInvoiceComponents()) {
                        $invoiceComponents = array();
                        foreach($invoiceProduct->getInvoiceComponents() as $invoiceComponent) {
                            if (!$invoiceComponent->getSuppressed()) {
                                $invoiceComponents[] = $this->mainService->toArray($invoiceComponent->toArray());
                            }
                        }
                        $invoiceProducts[$i]['invoiceComponents'] = $invoiceComponents;
                    }
                    $i++;
                }
            }
            $objectArray['invoiceProducts'] = $invoiceProducts;
        }

        return $objectArray;
    }
}

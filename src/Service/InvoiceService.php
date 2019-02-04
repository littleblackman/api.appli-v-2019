<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\InvoiceComponent;
use App\Entity\InvoiceProduct;
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
        MainServiceInterface $mainService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
    }

    /**
     * Adds link between Invoice and InvoiceProduct
     */
    public function addInvoiceProduct($data, Invoice $object)
    {
        $invoiceProduct = new InvoiceProduct();
        $this->mainService->create($invoiceProduct);
        $invoiceProduct
            ->setInvoice($object)
            ->setNameFr(array_key_exists('nameFr', $data) ? $data['nameFr'] : null)
            ->setNameEn(array_key_exists('nameEn', $data) ? $data['nameEn'] : null)
            ->setDescriptionFr(array_key_exists('descriptionFr', $data) ? $data['descriptionFr'] : null)
            ->setDescriptionEn(array_key_exists('descriptionEn', $data) ? $data['descriptionEn'] : null)
            ->setPriceTtc(array_key_exists('priceTtc', $data) ? $data['priceTtc'] : null)
            ->setPrices(array_key_exists('prices', $data) ? $data['prices'] : null)
        ;

        //Persists data
        $this->mainService->persist($invoiceProduct);

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
        $invoiceComponent = new InvoiceComponent();
        $this->mainService->create($invoiceComponent);
        $invoiceComponent
            ->setInvoiceProduct($object)
            ->setNameFr(array_key_exists('nameFr', $data) ? $data['nameFr'] : null)
            ->setNameEn(array_key_exists('nameEn', $data) ? $data['nameEn'] : null)
            ->setPriceHt(array_key_exists('priceHt', $data) ? $data['priceHt'] : null)
            ->setPriceVat(array_key_exists('priceVat', $data) ? $data['priceVat'] : null)
            ->setPriceTtc(array_key_exists('priceTtc', $data) ? $data['priceTtc'] : null)
            ->setQuantity(array_key_exists('quantity', $data) ? $data['quantity'] : null)
            ->setTotalHt(array_key_exists('totalHt', $data) ? $data['totalHt'] : null)
            ->setTotalVat(array_key_exists('totalVat', $data) ? $data['totalVat'] : null)
            ->setTotalTtc(array_key_exists('totalTtc', $data) ? $data['totalTtc'] : null)
        ;

        //Persists data
        $this->mainService->persist($invoiceComponent);
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
     * {@inheritdoc}
     */
    public function isEntityFilled(Invoice $object)
    {
        if (null === $object->getNameFr() ||
            null === $object->getNumber()) {
            throw new UnprocessableEntityHttpException('Missing data for Invoice -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Invoice $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'invoice-modify', $data);
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

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
        $invoiceProducts = $object->getInvoiceProducts();
        if (null !== $invoiceProducts && !empty($invoiceProducts)) {
            foreach ($invoiceProducts as $invoiceProduct) {
                //Removes links to invoiceComponents
                $invoiceComponents = $invoiceProduct->getInvoiceComponents();
                if (null !== $invoiceComponents && !empty($invoiceComponents)) {
                    foreach ($invoiceComponents as $invoiceComponent) {
                        $this->mainService->delete($invoiceComponent);
                        $this->mainService->persist($invoiceComponent);
                    }
                }
                $this->mainService->delete($invoiceProduct);
                $this->mainService->persist($invoiceProduct);
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

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

    public function createManuel(string $sendData) {
        $dataArray = is_array($sendData) ? $sendData : json_decode($sendData, true);

        // test person first
        if(isset($dataArray['person'])) {
            if(!$person = $this->em->getRepository('App:Person')->find($dataArray['person'])) return ['message' => "person_data_send_but_not_founded"];
        }

        // test child if exist
        if(isset($dataArray['child'])) {
            if(!$child = $this->em->getRepository('App:Child')->find($dataArray['child'])) return ['message' => "child_data_send_but_no_founded"];
        } else {
            $child = null;
        }

        // create person if from child if exist
        if(!isset($person) && isset($child)) {
            if($child == null) return "no_child_data_send_no_person_data_send";
            if(!$person = $child->getPersons()[0]->getPerson()) return ['message' => "child_founded_but_no_person_founded"];
        }


        (isset($dataArray['nameFr'])) ? $nameFr = $dataArray['nameFr'] : $nameFr = null;
        (isset($dataArray['nameEn'])) ? $nameEn = $dataArray['nameEn'] : $nameEn = null;
        (isset($dataArray['date'])) ? $date = new DateTime($dataArray['date']): $date = new DateTime();

        (isset($dataArray['paymentMethod'])) ? $paymentMethod = $dataArray['paymentMethod'] : $paymentMethod = "cb";


        (isset($dataArray['address']))  ? $address  = $dataArray['address'] : $address = null;
        (isset($dataArray['postal']))   ? $postal   = $dataArray['postal'] : $postal = null;
        (isset($dataArray['town']))     ? $town     = $dataArray['town'] : $town = null;
        (isset($dataArray['priceTtc'])) ? $priceTtc = $dataArray['priceTtc'] : $priceTtc = null;



        (isset($dataArray['descriptionFr'])) ? $descriptionFr = $dataArray['descriptionFr'] : $descriptionFr = null;
        (isset($dataArray['descriptionEn'])) ? $descriptionEn = $dataArray['descriptionEn'] : $descriptionEn = null;

        // set status to payed if no status
        (isset($dataArray['status'])) ? $status = $dataArray['status'] : $status = "payed";
        (isset($dataArray['number'])) ? $number = $dataArray['number'] : $number = null;
                  
        $invoice = new Invoice();
        $invoice->setPerson($person);
        $invoice->setChild($child);
        $invoice->setNameFr($nameFr);
        $invoice->setNameEn($nameEn);
        $invoice->setDescriptionFr($descriptionFr);
        $invoice->setDescriptionEn($descriptionEn);
        $invoice->setDate($date);
        $invoice->setStatus($status);
        $invoice->setPaymentMethod($paymentMethod);
        $invoice->setAddress($address);
        $invoice->setPostal($postal);
        $invoice->setTown($town);
        $invoice->setPriceTtc($priceTtc);
        $invoice->setNumber($number);

        //Persists data
        $this->mainService->create($invoice);
        $this->mainService->persist($invoice);

        /*
       "invoiceProducts":[
           {"nameFr":"Matin - stage","descriptionFr":"Matin\u00e9e \u00e0 la carte","priceTtc":"130","priceHt":"","totalTtc":"130","totalHt":"","
            invoiceComponents":[{"nameFr":"Repas collation","nameEn":" ","priceHt":"10.91","quantity":"1","vat":"10","priceVat":"1.09","priceTtc":"12","totalHt":"10.91","totalTtc":"12"},
            {"nameFr":"Transport \u00e0 domicile","nameEn":"Test ","priceHt":"62.73","quantity":"1","vat":"10","priceVat":"6.27","priceTtc":"69","totalHt":"62.73","totalTtc":"69"},{"nameFr":"Enseignement sportif - \u00e9cole","nameEn":"test ","priceHt":"40.83","quantity":"1","vat":"20","priceVat":"8.17","priceTtc":"49","totalHt":"40.83","totalTtc":"49"}],"quantity":"1"},{"nameFr":"Anniversaire, Sorties","descriptionFr":"Anniversaire, Sorties","priceTtc":"20","priceHt":"16","totalTtc":"20","totalHt":"16","invoiceComponents":[{"nameFr":"Anniversaire, Sorties","priceHt":"16","quantity":"1","vat":"20","priceVat":"4","priceTtc":"20","totalHt":"16","totalTtc":"20"}],"quantity":"1"}]}
*/


        if(isset($dataArray['invoiceProducts'])) {

            foreach($dataArray['invoiceProducts'] as $product) {
        

                $description = [
                    'registration_id' => null,
                    'child_id' => $child->getChildId(),
                    'child_name' => $child->getFullname(),
                    'dates' => implode('|', [$dataArray['date']])
                ];
    
    
                $invoiceProduct = new InvoiceProduct();
                $invoiceProduct->setInvoice($invoice);
                $invoiceProduct->setNameFr($product['nameFr']);
                $invoiceProduct->setDescriptionFr(serialize($description));
                $invoiceProduct->setPriceTtc($product['priceTtc']);
                $invoiceProduct->setQuantity($product['quantity']);  // check NB SESSION IN REGISTRATIONS
                
                $this->mainService->modify($invoiceProduct);
                $this->mainService->persist($invoiceProduct);
    
    
                foreach($product['invoiceComponents'] as $component) {
    
                    $invoiceComponent = new InvoiceComponent();
                    $invoiceComponent->setInvoiceProduct($invoiceProduct);
                    $invoiceComponent->setNameFr($component['nameFr']);
                    $invoiceComponent->setVat($component['vat']);
                    $invoiceComponent->setPriceHt($component['priceHt']);
                    $invoiceComponent->setPriceVat($component['priceVat']);
                    $invoiceComponent->setPriceTtc($component['priceTtc']);
                    $invoiceComponent->setQuantity($component['quantity']);
                    $invoiceComponent->setTotalHt($component['totalHt']);
                   // $invoiceComponent->setTotalVat($component['totalVat']);
                    $invoiceComponent->setTotalTtc($component['totalTtc']);
    
                    $this->mainService->modify($invoiceComponent);
                    $this->mainService->persist($invoiceComponent);
    
                    $invoiceProduct->addInvoiceComponent($invoiceComponent);
    
                    $this->mainService->modify($invoiceProduct);
                    $this->mainService->persist($invoiceProduct);
    
    
                }
    
                $invoice->addInvoiceProduct($invoiceProduct);
    
                $this->mainService->modify($invoice);
                $this->mainService->persist($invoice);
    
            }

        }


        if($status == "payed") $invoice = $this->updateInvoiceNumber($invoice);

        return array(
            'status' => true,
            'message' => 'Facture ajoutée',
            'invoice' => $this->toArray($invoice),
        );


    }


    public function updateInvoiceNumber($invoice) {
        $parameter = $this->em->getRepository('App:Parameter')->findOneBy(['name' => 'last_invoice_number']);

        $newNumber = $parameter->getValue() + 1;

        $invoice->setNumber($newNumber);
        $parameter->setValue($newNumber);

        $this->mainService->persist($invoice);

        $this->mainService->persist($parameter);

        return $invoice;
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
    public function findAll($status = "payed", $limit = 500, $dateStart = null, $dateEnd = null, $mode = 'all')
    {
        if(!$dateStart) {
            $year = date('Y')-1;
            $d    = date('m-d');
            $dateStart = $year.'-'.$d;

        }
        if(!$dateEnd)  {
            $date = new DateTime('+1 day');
            $dateEnd = $date->format('Y-m-d');;
        }

        $invoices = $this->em->getRepository('App:Invoice')->findByStatus($dateStart, $dateEnd, $status, $mode);


        $invoicesArray = array();

        foreach ($invoices as $invoice) {
            if($transaction = $this->em->getRepository('App:Transaction')->findOneBy(['invoice' => $invoice])) {
                $invoice->setTransaction($transaction);
            }
            $invoicesArray[] = $this->toArray($invoice);
        };

        return $invoicesArray;
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
        if (null === $object->getNameFr()) {
            throw new UnprocessableEntityHttpException('Missing data for Invoice -> ' . json_encode($object->toArray()));
        }
    }

    public function addComponentInvoiceProduct(string $data) {
        $dataArray = is_array($data) ? $data : json_decode($data, true);

        // retrieve invoice product, component & invoice
        $invoiceProduct = $this->em->getRepository('App:InvoiceProduct')->find($dataArray['invoiceProductId']);
        $component = $this->em->getRepository('App:Component')->find($dataArray['componentId']);
        $invoice = $invoiceProduct->getInvoice();
        
        // calcul all prices
        $priceTtc = $dataArray['price'];
        $quantity = $dataArray['quantity'];

        $vat= $component->getVat();
        $priceHt = $priceTtc*100/($vat+100);

        $totalHt = $priceHt*$quantity;
        $totalTtc = $priceTtc*$quantity;

        // create invoice comp
        $invoiceComponent = new InvoiceComponent();
        $invoiceComponent->setInvoiceProduct($invoiceProduct);
        $invoiceComponent->setNameFr($component->getNameFr());
        $invoiceComponent->setVat($vat);
        $invoiceComponent->setPriceHt($priceHt);
        $invoiceComponent->setPriceVat($priceTtc-$priceHt);
        $invoiceComponent->setPriceTtc($priceTtc);
        $invoiceComponent->setQuantity($quantity);
        $invoiceComponent->setTotalHt($totalHt);
        $invoiceComponent->setTotalVat($totalTtc-$totalHt);
        $invoiceComponent->setTotalTtc($totalTtc);

        $this->mainService->modify($invoiceComponent);
        $this->mainService->persist($invoiceComponent);

        // Add comp to invoiceProduct
        $invoiceProduct->addInvoiceComponent($invoiceComponent);
        $invoiceProduct->setPriceTtc($invoiceProduct->getPriceTtc()+$totalTtc);

        $this->mainService->modify($invoiceProduct);
        $this->mainService->persist($invoiceProduct);

        // update invoice total
        $invoice->setPriceTtc($invoice->getPriceTtc()+$totalTtc);
        $this->mainService->modify($invoice);
        $this->mainService->persist($invoice);


        return $invoiceComponent->toArray();

    }



    public function deleteComponentInvoiceProduct(string $data) {
        $dataArray = is_array($data) ? $data : json_decode($data, true);

        // retrieve invoice product, component & invoice
        $component = $this->em->getRepository('App:InvoiceComponent')->find($dataArray['invoiceComponentId']);

        $componentArray = $component->toArray();

        $invoiceProduct = $component->getInvoiceProduct();
        $invoice = $invoiceProduct->getInvoice();
        
        // delete totalto invoiceProduct
        $invoiceProduct->removeInvoiceComponent($component);
        $invoiceProduct->setPriceTtc($invoiceProduct->getPriceTtc()-$component->getTotalttc());

        $this->mainService->modify($invoiceProduct);
        $this->mainService->persist($invoiceProduct);

        // update invoice total
        $invoice->setPriceTtc($invoice->getPriceTtc()-$component->getTotalttc());
        $this->mainService->modify($invoice);
        $this->mainService->persist($invoice);


        return $componentArray;

    }






    /**
     * {@inheritdoc}
     */
    public function modify(Invoice $object, string $data)
    {

        $firstStatus = $object->getStatus();
        

        //Submits data
        $data = $this->mainService->submit($object, 'invoice-modify', $data);
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);


        if($object->getStatus() == "payed" && $object->getStatus() != $firstStatus) {
            $parameter = $this->em->getRepository('App:Parameter')->findOneBy(['name' => 'last_invoice_number']);

            $newNumber = $parameter->getValue() + 1;

            $object->setNumber($newNumber);
            $parameter->setValue($newNumber);

            $this->mainService->persist($object);

            $this->mainService->persist($parameter);


        } 



        if($object->getStatus() == 'paiementInProgress') {
  
            // update registration
            $registrations = $this->em->getRepository('App:Registration')->findByInvoice($object);
            foreach($registrations as $registration) {
                    $product = $registration->getProduct();

                    if($product->getPersonalStatus() == "awaiting") {
                        $product->setPersonalStatus('payed');
                    }

                    $this->em->persist($product);
                    $this->em->flush();


                    $registration->setPayed(floatval($product->getPriceTtc()));
                    $this->mainService->modify($registration);
                    $this->mainService->persist($registration);

                    foreach($registration->getSessions() as $session) {
                        $sessiondate[] = $session['date'];
                    }

                    $description = [
                                    'registration_id' => $registration->getRegistrationId(),
                                    'child_id' => $registration->getChild()->getChildId(),
                                    'child_name' => $registration->getChild()->getFullname(),
                                    'dates' => implode('|', $sessiondate)
                    ];

                    unset($sessiondate);

                    $invoiceProduct = new InvoiceProduct();
                    $invoiceProduct->setInvoice($object);
                    $invoiceProduct->setNameFr($product->getNameFr());
                    $invoiceProduct->setDescriptionFr(serialize($description));
                    $invoiceProduct->setPriceTtc($product->getPriceTtc());
                    $invoiceProduct->setQuantity(1);  // check NB SESSION IN REGISTRATIONS
                    
                    $this->mainService->modify($invoiceProduct);
                    $this->mainService->persist($invoiceProduct);


                    foreach($product->getComponents() as $component) {

                        $invoiceComponent = new InvoiceComponent();
                        $invoiceComponent->setInvoiceProduct($invoiceProduct);
                        $invoiceComponent->setNameFr($component->getNameFr());
                        $invoiceComponent->setVat($component->getVat());
                        $invoiceComponent->setPriceHt($component->getPriceHt());
                        $invoiceComponent->setPriceVat($component->getPriceVat());
                        $invoiceComponent->setPriceTtc($component->getPriceTtc());
                        $invoiceComponent->setQuantity($component->getQuantity());
                        $invoiceComponent->setTotalHt($component->getTotalHt());
                        $invoiceComponent->setTotalVat($component->getTotalVat());
                        $invoiceComponent->setTotalTtc($component->getTotalTtc());

                        $this->mainService->modify($invoiceComponent);
                        $this->mainService->persist($invoiceComponent);

                        $invoiceProduct->addInvoiceComponent($invoiceComponent);

                        $this->mainService->modify($invoiceProduct);
                        $this->mainService->persist($invoiceProduct);
    

                    }

                    $object->addInvoiceProduct($invoiceProduct);

                    $this->mainService->modify($object);
                    $this->mainService->persist($object);


            }

            
        }


        // update transaction and registrations
        if($object->getStatus() != $firstStatus) {
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

    public function finByPerson($person_id, $year) {

        $person = $this->em->getRepository('App:Person')->find($person_id);


        $invoices = $this->em->getRepository('App:Invoice')->findByPerson($person, $year);

        $invoicesArray = array();

        foreach ($invoices as $invoice) {
            if($transaction = $this->em->getRepository('App:Transaction')->findOneBy(['invoice' => $invoice])) {
                $invoice->setTransaction($transaction);
            }
            $invoicesArray[] = $this->toArray($invoice);
        };

        return $invoicesArray;
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


        if($objectArray['transaction']) {
            $objectArray['transaction'] = $invoice->getTransaction()->toArray();
        }

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

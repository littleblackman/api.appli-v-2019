<?php

namespace App\Service;

use App\Entity\Component;
use App\Entity\Product;
use App\Entity\ProductComponentLink;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * ProductService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ProductService implements ProductServiceInterface
{
    private $componentService;

    private $em;

    private $mainService;

    public function __construct(
        ComponentServiceInterface $componentService,
        EntityManagerInterface $em,
        MainServiceInterface $mainService
    )
    {
        $this->componentService = $componentService;
        $this->em = $em;
        $this->mainService = $mainService;
    }

    /**
     * Adds link betwenn Product and Component
     */
    public function addLink(int $componentId, Product $object)
    {
        $component = $this->em->getRepository('App:Component')->findOneById($componentId);
        if ($component instanceof Component && !$component->getSuppressed()) {
            $productComponentLink = new ProductComponentLink();
            $productComponentLink
                ->setProduct($object)
                ->setComponent($component)
            ;
            $this->em->persist($productComponentLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new Product();
        $data = $this->mainService->submit($object, 'product-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->create($object);
        $this->mainService->persist($object);

        //Adds links from component/s to product
        if (isset($data['links'])) {
            $links = $data['links'];

            if (null !== $links && is_array($links) && !empty($links)) {
                foreach ($links as $link) {
                    $this->addLink((int) $link['componentId'], $object);
                }

                //Persists in DB
                $this->em->flush();
                $this->em->refresh($object);
            }
        }

        //Returns data
        return array(
            'status' => true,
            'message' => 'Produit ajouté',
            'product' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Product $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        //Removes links from product to components
        $links = $object->getComponents();
        if (null !== $links && !empty($links)) {
            foreach ($links as $link) {
                $this->em->remove($link);
            }

            //Persists in DB
            $this->em->flush();
            $this->em->refresh($object);
        }

        return array(
            'status' => true,
            'message' => 'Produit supprimé',
        );
    }

    /**
     * Returns the list of all products in the array format
     * @return array
     */
    public function findAll()
    {
        return $this->em
            ->getRepository('App:Product')
            ->findAll()
        ;
    }

    /**
     * Searches the term in the Product collection
     * @return array
     */
    public function findAllSearch(string $term)
    {
        return $this->em
            ->getRepository('App:Product')
            ->findAllSearch($term)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Product $object)
    {
        if (null === $object->getNameFr() ||
            null === $object->getDescriptionFr()) {
            throw new UnprocessableEntityHttpException('Missing data for Product -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Product $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'product-modify', $data);
        $fieldsArray = array(
            'daysAvailable',
            'duration',
            'expectedTimes',
        );
        foreach ($fieldsArray as $field) {
            if (isset($data[$field]) && null !== $data[$field]) {
                $method = 'set' . ucfirst($field);
                if (method_exists($object, $method)) {
                    $object->$method($data[$field]);
                }
            }
        }

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Produit modifié',
            'product' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Product $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related family
        if (null !== $object->getFamily() && !$object->getFamily()->getSuppressed()) {
            $objectArray['family'] = $this->mainService->toArray($object->getFamily()->toArray());
        }

        //Gets related season
        if (null !== $object->getSeason() && !$object->getSeason()->getSuppressed()) {
            $objectArray['season'] = $this->mainService->toArray($object->getSeason()->toArray());
        }

        //Gets related location
        if (null !== $object->getLocation() && !$object->getLocation()->getSuppressed()) {
            $objectArray['location'] = $this->mainService->toArray($object->getLocation()->toArray());
        }

        //Gets related components
        if (null !== $object->getComponents()) {
            $components = array();
            foreach($object->getComponents() as $componentLink) {
                if (!$componentLink->getComponent()->getSuppressed()) {
                    $components[] = $this->mainService->toArray($componentLink->getComponent()->toArray());
                }
            }
            $objectArray['components'] = $components;
        }

        return $objectArray;
    }
}

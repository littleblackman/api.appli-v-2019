<?php

namespace App\Service;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * CategoryService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class CategoryService implements CategoryServiceInterface
{
    private $em;

    private $mainService;

    private $productService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService,
        ProductServiceInterface $productService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
        $this->productService = $productService;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new Category();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'category-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Catégorie ajoutée',
            'category' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Category $object)
    {
        //Removes links to products
        $products = $object->getProducts();
        if (null !== $products && !empty($products)) {
            foreach ($products as $productLink) {
                $this->em->remove($productLink);
            }
        }

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'Catégorie supprimée',
        );
    }

    /**
     * Returns the list of all families in the array format
     * @return array
     */
    public function findAll()
    {
        return $this->em
            ->getRepository('App:Category')
            ->findAll()
        ;
    }

    /**
     * Searches the term in the Category collection
     * @return array
     */
    public function findAllSearch(string $term)
    {
        return $this->em
            ->getRepository('App:Category')
            ->findAllSearch($term)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Category $object)
    {
        if (null === $object->getName()) {
            throw new UnprocessableEntityHttpException('Missing data for Category -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Category $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'category-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Catégorie modifiée',
            'category' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Category $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related products
        if (null !== $object->getProducts()) {
            $products = array();
            foreach($object->getProducts() as $productLink) {
                if (!$productLink->getProduct()->getSuppressed()) {
                    $products[] = $this->mainService->toArray($productLink->getProduct()->toArray());
                }
            }
            $objectArray['products'] = $products;
        }

        return $objectArray;
    }
}

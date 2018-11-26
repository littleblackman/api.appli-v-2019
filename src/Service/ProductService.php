<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Component;
use App\Entity\Product;
use App\Entity\ProductComponentLink;
use App\Form\AppFormFactoryInterface;
use App\Service\ComponentServiceInterface;
use App\Service\ProductServiceInterface;

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
     * {@inheritdoc}
     */
    public function addLink(int $componentId, Product $object)
    {
        $component = $this->em->getRepository('App:Component')->findOneById($componentId);
        if ($component instanceof Component) {
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
    public function create(Product $object, string $data)
    {
        //Submits data
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
        if (isset($data['links'])) {
            $links = $data['links'];

            if (null !== $links && is_array($links) && !empty($links)) {
                foreach ($links as $link) {
                    $this->removeLink((int) $link['componentId'], $object);
                }

                //Persists in DB
                $this->em->flush();
                $this->em->refresh($object);
            }
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
    public function removeLink(int $componentId, Product $object)
    {
        $component = $this->em->getRepository('App:Component')->findOneById($componentId);
        if ($component instanceof Component) {
            $productComponentLink = $this->em->getRepository('App:ProductComponentLink')->findOneBy(array('product' => $object, 'component' => $component));
            $this->em->remove($productComponentLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Product $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related season
        if (null !== $object->getSeason()) {
            $objectArray['season'] = $this->mainService->toArray($object->getSeason()->toArray());
        }

        //Gets related location
        if (null !== $object->getLocation()) {
            $objectArray['location'] = $this->mainService->toArray($object->getLocation()->toArray());
        }

        //Gets related components
        if (null !== $object->getComponents()) {
            $components = array();
            foreach($object->getComponents() as $componentLink) {
                $components[] = $this->mainService->toArray($componentLink->getComponent()->toArray());
            }
            $objectArray['components'] = $components;
        }

        return $objectArray;
    }
}

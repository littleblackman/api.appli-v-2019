<?php

namespace App\Service;

use App\Entity\Product;

/**
 * ProductServiceInterface class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
interface ProductServiceInterface
{
    /**
     * Adds the links from product to component
     * @return array
     */
    public function addLink(int $componentId, Product $object);

    /**
     * Creates the product
     * @return array
     */
    public function create(Product $object, string $data);

    /**
     * Marks the product as deleted
     * @return array
     */
    public function delete(Product $object);

    /**
     * Checks if the entity has been well filled
     * @throw Exception
     */
    public function isEntityFilled(Product $object);

    /**
     * Modifies the product
     * @return array
     */
    public function modify(Product $object, string $data);

    /**
     * Removes the links from product to component
     * @return array
     */
    public function removeLink(int $componentId, Product $object);

    /**
     * Converts entity to array
     * @return array
     */
    public function toArray(Product $object);
}

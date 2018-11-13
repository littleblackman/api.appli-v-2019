<?php

namespace App\Service;

use App\Entity\Address;

/**
 * AddressServiceInterface class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
interface AddressServiceInterface
{
    /**
     * Adds the links from person to address
     * @return array
     */
    public function addLink(int $personId, Address $object);

    /**
     * Creates the address
     * @return array
     */
    public function create(Address $object, string $data);

    /**
     * Marks the address as deleted
     * @return array
     */
    public function delete(Address $object, string $data);

    /**
     * Checks if the entity has been well filled
     * @throw Exception
     */
    public function isEntityFilled(Address $object);

    /**
     * Modifies the address
     * @return array
     */
    public function modify(Address $object, string $data);

    /**
     * Removes the links from person to address
     * @return array
     */
    public function removeLink(int $personId, Address $object);

    /**
     * Converts entity to array
     * @return array
     */
    public function toArray(Address $object);
}

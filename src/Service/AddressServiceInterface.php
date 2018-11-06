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
    public function addLink(int $personId, Address $address);

    /**
     * Creates the address
     * @return array
     */
    public function create(Address $address, string $data);

    /**
     * Marks the address as deleted
     * @return array
     */
    public function delete(Address $address, string $data);

    /**
     * Filters the array to return only data allowed to User's Role
     * @return array
     */
    public function filter(array $addressArray);

    /**
     * Returns the list of all addresses in the array format
     */
    public function getAllInArray();

    /**
     * Checks if the entity has been well filled
     * @throw Exception
     */
    public function isEntityFilled(Address $address);

    /**
     * Modifies the address
     * @return array
     */
    public function modify(Address $address, string $data);

    /**
     * Removes the links from person to address
     * @return array
     */
    public function removeLink(int $personId, Address $address);
}

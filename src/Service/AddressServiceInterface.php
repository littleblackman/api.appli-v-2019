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
     * Creates the <address>
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
     * Modifies the address
     * @return array
     */
    public function modify(Address $address, string $data);
}

<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\ParameterBag;
use App\Entity\Address;

interface AddressServiceInterface
{
    /**
     * Creates the <address>
         @return array|false
     </address>
     */
    public function create(Address $address, ParameterBag $parameters);

    /**
     * Marks the address as deleted
     * @return array
     */
    public function delete(Address $address, ParameterBag $parameters);

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
     * Hydrates the address with the new values
     */
    public function hydrate(Address $address, ParameterBag $parameters);

    /**
     * Modifies the address
     * @return array
     */
    public function modify(Address $address, ParameterBag $parameters);
}

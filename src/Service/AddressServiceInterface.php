<?php

namespace App\Service;

interface AddressServiceInterface
{
    /**
     * Filters the array to return only data allowed to User's Role
     * @return array
     */
    public function filter(array $addressArray);

    /**
     * Returns the list of all addresses in the array format
     */
    public function getAllInArray();
}

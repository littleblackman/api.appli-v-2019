<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\ParameterBag;
use App\Entity\Person;

interface PersonServiceInterface
{
    /**
     * Marks the person as deleted
     * @return array
     */
    public function delete(Person $person);

    /**
     * Filters the array to return only data allowed to User's Role
     * @return array
     */
    public function filter(array $personArray);

    /**
     * Returns the list of all persons in the array format
     */
    public function getAllInArray();

    /**
     * Hydrates the person with the new values
     * @return true|array
     */
    public function hydrate(Person $person, ParameterBag $parameters);

    /**
     * Modifies the person
     * @return array
     */
    public function modify(Person $person, ParameterBag $parameters);

    /**
     * Searches the term in the Person collection
     * @return array
     */
    public function search(string $term, int $size);
}

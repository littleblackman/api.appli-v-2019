<?php

namespace App\Service;

use App\Entity\Person;

/**
 * PersonServiceInterface class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
interface PersonServiceInterface
{
    /**
     * Creates the person
     * @return array
     */
    public function create(Person $person, string $data);

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
     * @return array
     */
    public function findAllInArray();

    /**
     * Searches the term in the Child collection
     * @return array
     */
    public function findAllInSearch(string $term);

    /**
     * Checks if the entity has been well filled
     * @throw Exception
     */
    public function isEntityFilled(Person $person);

    /**
     * Modifies the person
     * @return array
     */
    public function modify(Person $person, string $data);
}

<?php

namespace App\Service;

use App\Entity\Child;

/**
 * ChildServiceInterface class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
interface ChildServiceInterface
{
    /**
     * Adds the links from person to child
     * @return array
     */
    public function addLink(int $personId, string $relation, Child $child);

    /**
     * Creates the child
     * @return array
     */
    public function create(Child $child, string $data);

    /**
     * Marks the child as deleted
     * @return array
     */
    public function delete(Child $child, string $data);

    /**
     * Filters the array to return only data allowed to User's Role
     * @return array
     */
    public function filter(array $childArray);

    /**
     * Returns the list of all children in the array format
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
    public function isEntityFilled(Child $child);

    /**
     * Modifies the child
     * @return array
     */
    public function modify(Child $child, string $data);

    /**
     * Removes the links from person to child
     * @return array
     */
    public function removeLink(int $personId, Child $child);
}

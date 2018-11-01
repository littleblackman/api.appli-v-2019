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
     * Creates the child
     * @return array|false
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
    public function getAllInArray();

    /**
     * Modifies the child
     * @return array
     */
    public function modify(Child $child, string $data);

    /**
     * Searches the term in the Child collection
     * @return array
     */
    public function search(string $term, int $size);
}

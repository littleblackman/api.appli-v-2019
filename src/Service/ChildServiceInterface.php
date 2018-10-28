<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\ParameterBag;
use App\Entity\Child;

interface ChildServiceInterface
{
    /**
     * Creates the child
     * @return array
     */
    public function create(Child $child, ParameterBag $parameters);

    /**
     * Marks the child as deleted
     * @return array
     */
    public function delete(Child $child);

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
     * Hydrates the child with the new values
     * @return true|array
     */
    public function hydrate(Child $child, ParameterBag $parameters);

    /**
     * Modifies the child
     * @return array
     */
    public function modify(Child $child, ParameterBag $parameters);

    /**
     * Searches the term in the Child collection
     * @return array
     */
    public function search(string $term, int $size);
}

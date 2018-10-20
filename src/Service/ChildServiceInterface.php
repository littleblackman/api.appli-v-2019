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
}

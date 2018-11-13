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
    public function addLink(int $personId, string $relation, Child $object);

    /**
     * Creates the child
     * @return array
     */
    public function create(Child $object, string $data);

    /**
     * Marks the child as deleted
     * @return array
     */
    public function delete(Child $object, string $data);

    /**
     * Checks if the entity has been well filled
     * @throw Exception
     */
    public function isEntityFilled(Child $object);

    /**
     * Modifies the child
     * @return array
     */
    public function modify(Child $object, string $data);

    /**
     * Removes the links from person to child
     * @return array
     */
    public function removeLink(int $personId, Child $object);

    /**
     * Converts entity to array
     * @return array
     */
    public function toArray(Child $object);
}

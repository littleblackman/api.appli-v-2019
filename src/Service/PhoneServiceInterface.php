<?php

namespace App\Service;

use App\Entity\Phone;

/**
 * PhoneServiceInterface class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
interface PhoneServiceInterface
{
    /**
     * Adds the links from person to phone
     * @return array
     */
    public function addLink(int $personId, Phone $object);

    /**
     * Creates the phone
     * @return array
     */
    public function create(Phone $object, string $data);

    /**
     * Marks the phone as deleted
     * @return array
     */
    public function delete(Phone $object, string $data);

    /**
     * Checks if the entity has been well filled
     * @throw Exception
     */
    public function isEntityFilled(Phone $object);

    /**
     * Modifies the phone
     * @return array
     */
    public function modify(Phone $object, string $data);

    /**
     * Removes the links from person to phone
     * @return array
     */
    public function removeLink(int $personId, Phone $object);

    /**
     * Converts entity to array
     * @return array
     */
    public function toArray(Phone $object);
}
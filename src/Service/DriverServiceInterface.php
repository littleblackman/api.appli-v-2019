<?php

namespace App\Service;

use App\Entity\Driver;

/**
 * DriverServiceInterface class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
interface DriverServiceInterface
{
    /**
     * Creates the driver
     * @return array
     */
    public function create(Driver $object, string $data);

    /**
     * Marks the driver as deleted
     * @return array
     */
    public function delete(Driver $object);

    /**
     * Checks if the entity has been well filled
     * @throw Exception
     */
    public function isEntityFilled(Driver $object);

    /**
     * Modifies the driver
     * @return array
     */
    public function modify(Driver $object, string $data);

    /**
     * Converts entity to array
     * @return array
     */
    public function toArray(Driver $object);
}

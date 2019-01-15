<?php

namespace App\Service;

use App\Entity\DriverPresence;

/**
 * DriverPresenceServiceInterface class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
interface DriverPresenceServiceInterface
{
    /**
     * Creates the driverPresence
     * @return array
     */
    public function create(string $data);

    /**
     * Marks the driverPresence as deleted
     * @return array
     */
    public function delete(DriverPresence $object);

    /**
     * Checks if the entity has been well filled
     * @throw Exception
     */
    public function isEntityFilled(DriverPresence $object);

    /**
     * Converts entity to array
     * @return array
     */
    public function toArray(DriverPresence $object);
}

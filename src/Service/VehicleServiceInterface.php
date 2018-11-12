<?php

namespace App\Service;

use App\Entity\Vehicle;

/**
 * VehicleServiceInterface class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
interface VehicleServiceInterface
{
    /**
     * Creates the vehicle
     * @return array
     */
    public function create(Vehicle $vehicle, string $data);

    /**
     * Marks the vehicle as deleted
     * @return array
     */
    public function delete(Vehicle $vehicle);

    /**
     * Filters the array to return only data allowed to User's Role
     * @return array
     */
    public function filter(array $vehicleArray);

    /**
     * Returns all the vehicles
     * @return array
     */
    public function findAllInArray();

    /**
     * Checks if the entity has been well filled
     * @throw Exception
     */
    public function isEntityFilled(Vehicle $vehicle);

    /**
     * Modifies the vehicle
     * @return array
     */
    public function modify(Vehicle $vehicle, string $data);
}

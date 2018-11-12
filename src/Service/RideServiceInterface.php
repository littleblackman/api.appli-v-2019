<?php

namespace App\Service;

use App\Entity\Person;
use App\Entity\Ride;

/**
 * RideServiceInterface class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
interface RideServiceInterface
{
    /**
     * Creates the ride
     * @return array
     */
    public function create(Ride $ride, string $data);

    /**
     * Marks the ride as deleted
     * @return array
     */
    public function delete(Ride $ride);

    /**
     * Filters the array to return only data allowed to User's Role
     * @return array
     */
    public function filter(array $rideArray);

    /**
     * Returns the list of all rides by date
     * @return array
     */
    public function findAllByDate(string $date);

    /**
     * Returns all the rides
     * @return array
     */
    public function findAllInArray(string $status);

    /**
     * Returns the ride linked to date and person
     * @return array
     */
    public function findOneByDateByPersonId(string $date, Person $person);

    /**
     * Checks if the entity has been well filled
     * @throw Exception
     */
    public function isEntityFilled(Ride $ride);

    /**
     * Modifies the ride
     * @return array
     */
    public function modify(Ride $ride, string $data);
}

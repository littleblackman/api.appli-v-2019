<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Person;
use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\UpdateTrait;
use App\Entity\Traits\SuppressionTrait;

/**
 * Ride
 *
 * @ORM\Table(name="ride", indexes={@ORM\Index(name="ride_vehicle_FK", columns={"vehicle_id"}), @ORM\Index(name="ride_user_FK", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\RideRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class Ride
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="ride_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $rideId;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     */
    private $name;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="start", type="time")
     */
    private $start;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="arrival", type="time")
     */
    private $arrival;

    /**
     * @var string
     *
     * @ORM\Column(name="start_point", type="string", length=256)
     */
    private $startPoint;

    /**
     * @var string
     *
     * @ORM\Column(name="end_point", type="string", length=256)
     */
    private $endPoint;

    /**
     * @var \Person
     *
     * @ORM\OneToOne(targetEntity="Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="person_id")
     */
    private $person;

    /**
     * @var \Vehicle
     *
     * @ORM\OneToOne(targetEntity="Vehicle")
     * @ORM\JoinColumn(name="vehicle_id", referencedColumnName="vehicle_id")
     */
    private $vehicle;

    private $pickups;

    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        $rideArray = get_object_vars($this);

        //Gets related person
        if (null !== $this->getPerson()) {
            $rideArray['person'] = $this->getPerson()->toArray();
        }

        //Gets related vehicle
        if (null !== $this->getVehicle()) {
            $rideArray['vehicle'] = $this->getVehicle()->toArray();
        }

        return $rideArray;
    }

    public function getRideId(): ?int
    {
        return $this->rideId;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getArrival(): ?\DateTimeInterface
    {
        return $this->arrival;
    }

    public function setArrival(\DateTimeInterface $arrival): self
    {
        $this->arrival = $arrival;

        return $this;
    }

    public function getStartPoint(): ?string
    {
        return $this->startPoint;
    }

    public function setStartPoint(string $startPoint): self
    {
        $this->startPoint = $startPoint;

        return $this;
    }

    public function getEndPoint(): ?string
    {
        return $this->endPoint;
    }

    public function setEndPoint(string $endPoint): self
    {
        $this->endPoint = $endPoint;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }
}

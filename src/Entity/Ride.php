<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use App\Entity\Person;
use App\Entity\Pickup;
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
     * @var App\Entity\Person
     *
     * @ORM\OneToOne(targetEntity="Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="person_id")
     */
    private $person;

    /**
     * @var App\Entity\Vehicle
     *
     * @ORM\OneToOne(targetEntity="Vehicle")
     * @ORM\JoinColumn(name="vehicle_id", referencedColumnName="vehicle_id")
     */
    private $vehicle;

    /**
     * @ORM\OneToMany(targetEntity="Pickup", mappedBy="ride")
     */
    private $pickups;

    public function __construct()
    {
        $this->pickups = new ArrayCollection();
    }

    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        $objectArray = get_object_vars($this);

        //Specific data
        if (null !== $objectArray['date']) {
            $objectArray['date'] = $objectArray['date']->format('Y-m-d');
        }
        if (null !== $objectArray['start']) {
            $objectArray['start'] = $objectArray['start']->format('H:i');
        }
        if (null !== $objectArray['arrival']) {
            $objectArray['arrival'] = $objectArray['arrival']->format('H:i');
        }

        return $objectArray;
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

    /**
     * @return Collection|RidePickupLink[]
     */
    public function getPickups(): Collection
    {
        return $this->pickups;
    }
}

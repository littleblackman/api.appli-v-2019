<?php

namespace App\Entity;

use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\SuppressionTrait;
use App\Entity\Traits\UpdateTrait;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
     * @var string|null
     *
     * @ORM\Column(name="kind", type="string", length=8, nullable=true)
     */
    private $kind;

    /**
     * @var DateTime|null
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
     * @var DateTime|null
     *
     * @ORM\Column(name="start", type="time")
     */
    private $start;

    /**
     * @var DateTime|null
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
     * @var App\Entity\Driver
     *
     * @ORM\OneToOne(targetEntity="Driver")
     * @ORM\JoinColumn(name="driver_id", referencedColumnName="driver_id")
     */
    private $driver;

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
            $objectArray['start'] = $objectArray['start']->format('H:i:s');
        }
        if (null !== $objectArray['arrival']) {
            $objectArray['arrival'] = $objectArray['arrival']->format('H:i:s');
        }

        return $objectArray;
    }

    public function getRideId(): ?int
    {
        return $this->rideId;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setKind($kind): self
    {
        $this->kind = $kind;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
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

    public function getStart()
    {
        return $this->start;
    }

    public function setStart($start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getArrival()
    {
        return $this->arrival;
    }

    public function setArrival($arrival): self
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

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(?Driver $driver): self
    {
        $this->driver = $driver;

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
     * @return Collection|Pickup[]
     */
    public function getPickups(): Collection
    {
        return $this->pickups;
    }

    public function addPickup(Pickup $pickup): self
    {
        if (!$this->pickups->contains($pickup)) {
            $this->pickups[] = $pickup;
            $pickup->setRide($this);
        }

        return $this;
    }

    public function removePickup(Pickup $pickup): self
    {
        if ($this->pickups->contains($pickup)) {
            $this->pickups->removeElement($pickup);
            // set the owning side to null (unless already changed)
            if ($pickup->getRide() === $this) {
                $pickup->setRide(null);
            }
        }

        return $this;
    }
}

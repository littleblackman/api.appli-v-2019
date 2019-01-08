<?php

namespace App\Entity;

use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\SuppressionTrait;
use App\Entity\Traits\UpdateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Driver
 *
 * @ORM\Table(name="driver", uniqueConstraints={@ORM\UniqueConstraint(name="driver_UN", columns={"person_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\DriverRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class Driver
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="driver_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $driverId;

    /**
     * @var Person
     *
     * @ORM\OneToOne(targetEntity="Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="person_id")
     */
    private $person;

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="integer", nullable=true)
     */
    private $priority;

    /**
     * @var Vehicle
     *
     * @ORM\OneToOne(targetEntity="Vehicle")
     * @ORM\JoinColumn(name="vehicle_id", referencedColumnName="vehicle_id")
     */
    private $vehicle;

    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="Address")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="address_id")
     */
    private $address;

    /**
     * @ORM\OneToMany(targetEntity="DriverZone", mappedBy="driver")
     */
    private $driverZones;

    public function __construct()
    {
        $this->driverZones = new ArrayCollection();
    }

    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        $objectArray = get_object_vars($this);

        //Specific data
        $objectArray['totalZones'] = $this->getDriverZones()->count();

        return $objectArray;
    }

    public function getDriverId(): ?int
    {
        return $this->driverId;
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

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

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

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection|DriverZone[]
     */
    public function getDriverZones(): Collection
    {
        return $this->driverZones;
    }

    public function addDriverZone(DriverZone $driverZone): self
    {
        if (!$this->driverZones->contains($driverZone)) {
            $this->driverZones[] = $driverZone;
            $driverZone->setDriver($this);
        }

        return $this;
    }

    public function removeDriverZone(DriverZone $driverZone): self
    {
        if ($this->driverZones->contains($driverZone)) {
            $this->driverZones->removeElement($driverZone);
            // set the owning side to null (unless already changed)
            if ($driverZone->getDriver() === $this) {
                $driverZone->setDriver(null);
            }
        }

        return $this;
    }
}

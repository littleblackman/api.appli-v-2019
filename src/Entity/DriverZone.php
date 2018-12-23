<?php

namespace App\Entity;

use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\SuppressionTrait;
use App\Entity\Traits\UpdateTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * DriverZone
 *
 * @ORM\Table(name="driver_zone")
 * @ORM\Entity(repositoryClass="App\Repository\DriverZoneRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverZone
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="driver_zone_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $driverZoneId;

    /**
     * @var App\Entity\Driver
     *
     * @ORM\OneToOne(targetEntity="Driver")
     * @ORM\JoinColumn(name="driver_id", referencedColumnName="driver_id")
     */
    private $driver;

    /**
     * @var string|null
     *
     * @ORM\Column(name="postal", type="string", length=5, nullable=true)
     */
    private $postal;

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="integer", nullable=true)
     */
    private $priority;

    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        $objectArray = get_object_vars($this);

        return $objectArray;
    }

    public function getDriverZoneId(): ?int
    {
        return $this->driverZoneId;
    }

    public function getPostal(): ?string
    {
        return $this->postal;
    }

    public function setPostal(?string $postal): self
    {
        $this->postal = $postal;

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

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(?Driver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\UpdateTrait;
use App\Entity\Traits\SuppressionTrait;

/**
 * Vehicle
 *
 * @ORM\Table(name="vehicle")
 * @ORM\Entity(repositoryClass="App\Repository\VehicleRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class Vehicle
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $vehicleId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="matriculation", type="string", length=16, nullable=false)
     */
    private $matriculation;

    /**
     * @var string|null
     *
     * @ORM\Column(name="combustible", type="string", length=16, nullable=true)
     */
    private $combustible;

    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    public function getVehicleId(): ?bool
    {
        return $this->vehicleId;
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

    public function getMatriculation(): ?string
    {
        return $this->matriculation;
    }

    public function setMatriculation(string $matriculation): self
    {
        $this->matriculation = $matriculation;

        return $this;
    }

    public function getCombustible(): ?string
    {
        return $this->combustible;
    }

    public function setCombustible(?string $combustible): self
    {
        $this->combustible = $combustible;

        return $this;
    }
}

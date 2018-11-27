<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\UpdateTrait;
use App\Entity\Traits\SuppressionTrait;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use App\Entity\Address;
use App\Entity\Child;
use App\Entity\UserDriverLink;

/**
 * Driver
 *
 * @ORM\Table(name="driver")
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
     * @var App\Entity\Person
     *
     * @ORM\OneToOne(targetEntity="Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="person_id")
     */
    private $person;

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

    public function getDriverId(): ?int
    {
        return $this->driverId;
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

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($priority): self
    {
        $this->priority = $priority;

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
}

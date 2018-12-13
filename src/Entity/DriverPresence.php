<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\UpdateTrait;
use App\Entity\Traits\SuppressionTrait;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

/**
 * DriverPresence
 *
 * @ORM\Table(name="driver_presence")
 * @ORM\Entity(repositoryClass="App\Repository\DriverPresenceRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverPresence
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="driver_presence_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $driverPresenceId;

    /**
     * @var App\Entity\Driver
     *
     * @ORM\OneToOne(targetEntity="Driver")
     * @ORM\JoinColumn(name="driver_id", referencedColumnName="driver_id")
     */
    private $driver;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="start", type="time")
     */
    private $start;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="end", type="time")
     */
    private $end;

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
        if (null !== $objectArray['end']) {
            $objectArray['end'] = $objectArray['end']->format('H:i:s');
        }

        return $objectArray;
    }

    public function getDriverPresenceId(): ?int
    {
        return $this->driverPresenceId;
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

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): self
    {
        $this->end = $end;

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

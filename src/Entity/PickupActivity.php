<?php

namespace App\Entity;

use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\SuppressionTrait;
use App\Entity\Traits\UpdateTrait;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * PickupActivity
 *
 * @ORM\Table(name="pickup_activity")
 * @ORM\Entity(repositoryClass="App\Repository\PickupActivityRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PickupActivity
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="pickup_activity_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $pickupActivityId;

    /**
     * @var Registration
     *
     * @ORM\OneToOne(targetEntity="Registration")
     * @ORM\JoinColumn(name="registration_id", referencedColumnName="registration_id")
     */
    private $registration;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="start", type="datetime")
     */
    private $start;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=32)
     */
    private $status;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="status_change", type="datetime")
     */
    private $statusChange;

    /**
     * @var string
     *
     * @ORM\Column(name="validated", type="string", length=16)
     */
    private $validated;

    /**
     * @var Child
     *
     * @ORM\OneToOne(targetEntity="Child")
     * @ORM\JoinColumn(name="child_id", referencedColumnName="child_id")
     */
    private $child;

    /**
     * @var Sport
     *
     * @ORM\OneToOne(targetEntity="Sport")
     * @ORM\JoinColumn(name="sport_id", referencedColumnName="sport_id")
     */
    private $sport;

    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        $objectArray = get_object_vars($this);

        //Specific data
        if (null !== $objectArray['start']) {
            $objectArray['start'] = $objectArray['start']->format('Y-m-d H:i');
        }
        if (null !== $objectArray['statusChange']) {
            $objectArray['statusChange'] = $objectArray['statusChange']->format('Y-m-d H:i:s');
        }

        return $objectArray;
    }

    public function getPickupActivityId(): ?int
    {
        return $this->pickupActivityId;
    }

    public function getRegistration(): ?Registration
    {
        return $this->registration;
    }

    public function setRegistration(?Registration $registration): self
    {
        $this->registration = $registration;

        return $this;
    }

    public function getStart(): ?DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(?DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusChange(): ?DateTimeInterface
    {
        return $this->statusChange;
    }

    public function setStatusChange(?DateTimeInterface $statusChange): self
    {
        $this->statusChange = $statusChange;

        return $this;
    }

    public function getValidated(): ?string
    {
        return $this->validated;
    }

    public function setValidated(?string $validated): self
    {
        $this->validated = $validated;

        return $this;
    }

    public function getChild(): ?Child
    {
        return $this->child;
    }

    public function setChild(?Child $child): self
    {
        $this->child = $child;

        return $this;
    }

    public function getSport(): ?Sport
    {
        return $this->sport;
    }

    public function setSport(?Sport $sport): self
    {
        $this->sport = $sport;

        return $this;
    }
}

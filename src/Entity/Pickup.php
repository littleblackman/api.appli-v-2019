<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\UpdateTrait;
use App\Entity\Traits\SuppressionTrait;

/**
 * Pickup
 *
 * @ORM\Table(name="pickup")
 * @ORM\Entity(repositoryClass="App\Repository\PickupRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class Pickup
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="pickup_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $pickupId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime")
     */
    private $start;

    /**
     * @var string|null
     *
     * @ORM\Column(name="address", type="string", length=256, nullable=true)
     */
    private $address;

    /**
     * @var integer|null
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    private $sortOrder;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=32)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="status_change", type="datetime")
     */
    private $statusChange;

    /**
     * @var integer|null
     *
     * @ORM\Column(name="places", type="integer", nullable=true)
     */
    private $places;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=32)
     */
    private $comment;

    /**
     * @var App\Entity\Child
     *
     * @ORM\OneToOne(targetEntity="Child")
     * @ORM\JoinColumn(name="child_id", referencedColumnName="child_id")
     */
    private $child;

    /**
     * @var App\Entity\Ride
     *
     * @ORM\ManyToOne(targetEntity="Ride")
     * @ORM\JoinColumn(name="ride_id", referencedColumnName="ride_id")
     */
    private $ride;

    public function __construct()
    {
        $this->rides = new ArrayCollection();
    }

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

    public function getPickupId(): ?int
    {
        return $this->pickupId;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(?int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusChange(): ?\DateTimeInterface
    {
        return $this->statusChange;
    }

    public function setStatusChange(\DateTimeInterface $statusChange): self
    {
        $this->statusChange = $statusChange;

        return $this;
    }

    public function getPlaces(): ?int
    {
        return $this->places;
    }

    public function setPlaces(?int $places): self
    {
        $this->places = $places;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

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

    public function getRide()
    {
        return $this->ride;
    }

    public function setRide($ride)
    {
dump($ride);
        $this->ride = $ride;

        return $this;
    }
}

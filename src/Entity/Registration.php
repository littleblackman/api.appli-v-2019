<?php

namespace App\Entity;

use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\SuppressionTrait;
use App\Entity\Traits\UpdateTrait;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Registration
 *
 * @ORM\Table(name="registration")
 * @ORM\Entity(repositoryClass="App\Repository\RegistrationRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class Registration
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="registration_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $registrationId;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="registration", type="datetime", nullable=true)
     */
    private $registration;

    /**
     * @var Child
     *
     * @ORM\OneToOne(targetEntity="Child")
     * @ORM\JoinColumn(name="child_id", referencedColumnName="child_id")
     */
    private $child;

    /**
     * @var Person
     *
     * @ORM\OneToOne(targetEntity="Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="person_id")
     */
    private $person;

    /**
     * @var Product
     *
     * @ORM\OneToOne(targetEntity="Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id")
     */
    private $product;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_id", type="integer", nullable=true)
     */
    private $invoice;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_payed", type="boolean")
     */
    private $isPayed;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="session_date", type="date", nullable=true)
     */
    private $sessionDate;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="session_start", type="time", nullable=true)
     */
    private $sessionStart;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="session_end", type="time", nullable=true)
     */
    private $sessionEnd;

    /**
     * @var Product
     *
     * @ORM\OneToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="location_id")
     */
    private $location;


    /**
     * @var Product
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
        if (null !== $objectArray['registration']) {
            $objectArray['registration'] = $objectArray['registration']->format('Y-m-d');
        }
        if (null !== $objectArray['sessionDate']) {
            $objectArray['sessionDate'] = $objectArray['sessionDate']->format('Y-m-d');
        }
        if (null !== $objectArray['sessionStart']) {
            $objectArray['sessionStart'] = $objectArray['sessionStart']->format('H:i:s');
        }
        if (null !== $objectArray['sessionEnd']) {
            $objectArray['sessionEnd'] = $objectArray['sessionEnd']->format('H:i:s');
        }

        return $objectArray;
    }

    public function getRegistrationId(): ?int
    {
        return $this->registrationId;
    }

    public function getRegistration(): ?DateTimeInterface
    {
        return $this->registration;
    }

    public function setRegistration(?DateTimeInterface $registration): self
    {
        $this->registration = $registration;

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

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getInvoice(): ?int
    {
        return $this->invoice;
    }

    public function setInvoice(?int $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getIsPayed(): ?bool
    {
        return $this->isPayed;
    }

    public function setIsPayed(bool $isPayed): self
    {
        $this->isPayed = $isPayed;

        return $this;
    }

    public function getSessionDate(): ?DateTimeInterface
    {
        return $this->sessionDate;
    }

    public function setSessionDate(?DateTimeInterface $sessionDate): self
    {
        $this->sessionDate = $sessionDate;

        return $this;
    }

    public function getSessionStart(): ?DateTimeInterface
    {
        return $this->sessionStart;
    }

    public function setSessionStart(?DateTimeInterface $sessionStart): self
    {
        $this->sessionStart = $sessionStart;

        return $this;
    }

    public function getSessionEnd(): ?DateTimeInterface
    {
        return $this->sessionEnd;
    }

    public function setSessionEnd(?DateTimeInterface $sessionEnd): self
    {
        $this->sessionEnd = $sessionEnd;

        return $this;
    }
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

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

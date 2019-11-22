<?php

namespace App\Entity;

use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\SuppressionTrait;
use App\Entity\Traits\UpdateTrait;
use Doctrine\ORM\Mapping as ORM;
use DateTime;


/**
 * Vehicle
 *
 * @ORM\Table(name="vehicle_action")
 * @ORM\Entity(repositoryClass="App\Repository\VehicleActionRepository")
 *
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class VehicleAction
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Vehicle
     *
     * @ORM\OneToOne(targetEntity="Vehicle")
     * @ORM\JoinColumn(name="vehicle_id", referencedColumnName="vehicle_id")
     */
    private $vehicle;

    /**
     * @var string
     *
     * @ORM\Column(name="quantity", type="float", nullable=false)
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="float", nullable=false)
     */
    private $amount;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mileage", type="integer", nullable=true)
     */
    private $mileage;

    /**
     * @var Staff
     *
     * @ORM\OneToOne(targetEntity="Staff")
     * @ORM\JoinColumn(name="staff_id", referencedColumnName="staff_id")
     */
    private $staff;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="date_action", type="date", nullable=true)
     */
    private $dateAction;

    /**
     * @var String|null
     *
     * @ORM\Column(name="action_name", type="string", nullable=true)
     */
    private $actionName;

    /**
     * @var String|null
     *
     * @ORM\Column(name="action_type", type="string", nullable=true)
     */
    private $actionType;


    /**
     * Converts the entity in an array
     */
    public function toArray($type = "full")
    {
        $objectArray = get_object_vars($this);
        if($objectArray['staff']) {
            $objectArray['staff'] = $this->getStaff()->toArray($type);
        }
        if($objectArray['vehicle']) {
            $objectArray['vehicle'] = $this->getVehicle()->toArray($type);
        }

        if($type == "light") {

            unset($objectArray['__initializer__']);
            unset($objectArray['__cloner__']);
            unset($objectArray['__isInitialized__']);
            unset($objectArray['createdAt']);
            unset($objectArray['createdBy']);
            unset($objectArray['updatedBy']);
            unset($objectArray['updatedAt']);
            unset($objectArray['suppressedAt']);
            unset($objectArray['suppressedBy']);
            unset($objectArray['suppressed']);
        }

        return $objectArray;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(?string $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getMileage(): ?string
    {
        return $this->mileage;
    }

    public function setMileage(?string $mileage): self
    {
        $this->mileage = $mileage;
        return $this;
    }

    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    public function setStaff(?Staff $staff): self
    {
        $this->staff = $staff;

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

    public function getDateAction(): ?DateTimeInterface
    {
        return $this->dateAction;
    }

    public function setDateAction($dateAction)
    {
        if (!$dateAction instanceof DateTime) {
            $dateAction = new DateTime($dateAction);
        }

        $this->dateAction = $dateAction;

        return $this;
    }

    public function getActionName(): ?string
    {
        return $this->actionName;
    }

    public function setActionName(?string $actionName): self
    {
        $this->actionName = $actionName;

        return $this;
    }

    public function getActionType(): ?string
    {
        return $this->actionType;
    }

    public function setActionType(?string $actionType): self
    {
        $this->actionType = $actionType;

        return $this;
    }


}

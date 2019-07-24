<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\SuppressionTrait;
use App\Entity\Traits\UpdateTrait;

/**
 * @ORM\Table(name="task_staff")
 * @ORM\Entity(repositoryClass="App\Repository\TaskStaffRepository")
 */
class TaskStaff
{

    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Staff
     *
     * @ORM\OneToOne(targetEntity="Staff")
     * @ORM\JoinColumn(name="staff_id", referencedColumnName="staff_id", nullable=true)
     */
    private $staff;

    /**
     * @var Task|null
     *
     * @ORM\ManyToOne(targetEntity="Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true)
     */
    private $task;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    /**
    * @var string|null
    *
    * @ORM\Column(name="description", type="string", nullable=true)
    */
    private $description;

    /**
     * @var Staff
     *
     * @ORM\OneToOne(targetEntity="Staff")
     * @ORM\JoinColumn(name="supervisor_id", referencedColumnName="staff_id")
     */
    private $supervisor;

    /**
     * @ORM\Column(name="step", type="string", length=255, nullable=true)
     */
    private $step;

    /**
     * @ORM\Column(name="date_task", type="datetime", nullable=true)
     */
    private $dateTask;

    /**
     * @var string|null
     * @ORM\Column(name="remote_address", type="string", length=255, nullable=true)
     */
    private $remoteAddress;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateTask(): ?\DateTimeInterface
    {
        return $this->dateTask;
    }

    public function setDateTask(\DateTimeInterface $dateTask): self
    {
        $this->dateTask = $dateTask;

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

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): self
    {
        $this->task = $task;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStep(): ?string
    {
        return $this->step;
    }

    public function setStep(?string $step): self
    {
        $this->step = $step;

        return $this;
    }

    public function getSupervisor(): ?Staff
    {
        return $this->supervisor;
    }

    public function setSupervisor(?Staff $supervisor): self
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    public function getRemoteAddress(): ?string
    {
      return $this->remoteAddress;
    }

    public function setRemoteAddress($remoteAddress): self
    {
      $this->remoteAddress = $remoteAddress;
      return $this;
    }


    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        $objectArray = get_object_vars($this);
        if (null !== $objectArray['staff']) {
              $objectArray['staff'] = $this->getStaff()->toArray();
              $objectArray['staff_fullname'] = $this->getStaff()->getPerson()->getFirstname().' '.$this->getStaff()->getPerson()->getLastname();
        }
        if (null !== $objectArray['supervisor']) {
              $objectArray['supervisor'] = $this->getSupervisor()->toArray();
              $objectArray['supervisor_fullname'] = $this->getSupervisor()->getPerson()->getFirstname().' '.$this->getSupervisor()->getPerson()->getLastname();

        }

        return $objectArray;
    }
}

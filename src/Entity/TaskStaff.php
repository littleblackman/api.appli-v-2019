<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaskStaffRepository")
 */
class TaskStaff
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Staff
     *
     * @ORM\OneToOne(targetEntity="Staff")
     * @ORM\JoinColumn(name="staff_id", referencedColumnName="staff_id")
     */
    private $staff;

    /**
     * @var Task
     *
     * @ORM\OneToOne(targetEntity="Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="task_id")
     */
    private $task;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateTask;

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
}

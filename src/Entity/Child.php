<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Child
 *
 * @ORM\Table(name="child")
 * @ORM\Entity(repositoryClass="App\Repository\ChildRepository")
 */
class Child
{
    /**
     * @var int
     *
     * @ORM\Column(name="child_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $childId;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=64, nullable=false)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=64, nullable=false)
     */
    private $lastname;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone", type="string", length=35, nullable=true)
     */
    private $phone;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="birthdate", type="date", nullable=true)
     */
    private $birthdate;

    /**
     * @ORM\OneToMany(targetEntity="ChildPersonLink", mappedBy="child")
     */
    private $persons;

    /**
     * @ORM\OneToMany(targetEntity="ChildChildLink", mappedBy="child")
     */
    private $siblings;

    public function __construct()
    {
        $this->persons = new ArrayCollection();
        $this->siblings = new ArrayCollection();
    }

    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        $child = get_object_vars($this);

        //Gets related persons
        $persons = array();
        foreach($this->getPersons()->toArray() as $person) {
            $persons[] = $person->getPerson()->toArray();
        }
        $child['persons'] = $persons;

        //Gets related siblings
        $siblings = array();
        foreach($this->getSiblings()->toArray() as $sibling) {
            $siblings[] = $sibling->getSibling()->toArraySibling();
        }
        $child['siblings'] = $siblings;

        return $child;
    }

    /**
     * Converts the entity in an array without persons and siblings
     */
    public function toArraySibling()
    {
        $child = get_object_vars($this);
        unset($child['__initializer__']);
        unset($child['__cloner__']);
        unset($child['__isInitialized__']);

        unset($child['persons']);
        unset($child['siblings']);

        return $child;
    }

    public function getChildId(): ?int
    {
        return $this->childId;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getPersons(): Collection
    {
        return $this->persons;
    }

    public function getSiblings(): Collection
    {
        return $this->siblings;
    }
}

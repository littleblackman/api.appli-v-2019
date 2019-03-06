<?php

namespace App\Entity;

use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\SuppressionTrait;
use App\Entity\Traits\UpdateTrait;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

/**
 * Child
 *
 * @ORM\Table(name="child")
 * @ORM\Entity(repositoryClass="App\Repository\ChildRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class Child
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

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
     * @ORM\Column(name="gender", type="string", length=1, nullable=false)
     */
    private $gender;

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
     * @var DateTime|null
     *
     * @ORM\Column(name="birthdate", type="date", nullable=true)
     */
    private $birthdate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="medical", type="string", nullable=true)
     */
    private $medical;

    /**
     * @var string|null
     *
     * @ORM\Column(name="photo", type="string", length=256, nullable=true)
     */
    private $photo;

    /**
     * @var School
     *
     * @ORM\OneToOne(targetEntity="School")
     * @ORM\JoinColumn(name="school_id", referencedColumnName="school_id")
     */
    private $school;

    /**
     * @var boolean
     *
     * @ORM\Column(name="france_resident", type="boolean")
     */
    private $franceResident;

    /**
     * @ORM\OneToMany(targetEntity="ChildPersonLink", mappedBy="child")
     * @SWG\Property(ref=@Model(type=Person::class))
     */
    private $persons;

    /**
     * @ORM\OneToMany(targetEntity="ChildChildLink", mappedBy="child")
     * @SWG\Property(ref=@Model(type=Child::class))
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
        $objectArray = get_object_vars($this);

        //Specific data
        if (null !== $objectArray['birthdate']) {
            $objectArray['birthdate'] = $objectArray['birthdate']->format('Y-m-d');
        }

        return $objectArray;
    }

    public function getChildId(): ?int
    {
        return $this->childId;
    }

    public function getGender(): ?string
    {
        return null !== $this->gender ? strtolower($this->gender) : null;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = !empty($gender) && 'null' !== $gender ? strtolower($gender) : null;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
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

    public function getBirthdate(): ?DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate($birthdate)
    {
        if (!$birthdate instanceof DateTime) {
            $birthdate = new DateTime($birthdate);
        }

        $this->birthdate = $birthdate;

        return $this;
    }

    public function getMedical(): ?string
    {
        return $this->medical;
    }

    public function setMedical(?string $medical): self
    {
        $this->medical = $medical;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getFranceResident(): ?bool
    {
        return $this->franceResident;
    }

    public function setFranceResident(?bool $franceResident): self
    {
        $this->franceResident = $franceResident;

        return $this;
    }

    public function getPersons()
    {
        return $this->persons;
    }

    public function getSiblings()
    {
        return $this->siblings;
    }

    public function addPerson(ChildPersonLink $person): self
    {
        if (!$this->persons->contains($person)) {
            $this->persons[] = $person;
            $person->setChild($this);
        }

        return $this;
    }

    public function removePerson(ChildPersonLink $person): self
    {
        if ($this->persons->contains($person)) {
            $this->persons->removeElement($person);
            // set the owning side to null (unless already changed)
            if ($person->getChild() === $this) {
                $person->setChild(null);
            }
        }

        return $this;
    }

    public function addSibling(ChildChildLink $sibling): self
    {
        if (!$this->siblings->contains($sibling)) {
            $this->siblings[] = $sibling;
            $sibling->setChild($this);
        }

        return $this;
    }

    public function removeSibling(ChildChildLink $sibling): self
    {
        if ($this->siblings->contains($sibling)) {
            $this->siblings->removeElement($sibling);
            // set the owning side to null (unless already changed)
            if ($sibling->getChild() === $this) {
                $sibling->setChild(null);
            }
        }

        return $this;
    }
}

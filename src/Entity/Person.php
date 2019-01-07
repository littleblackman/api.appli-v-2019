<?php

namespace App\Entity;

use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\SuppressionTrait;
use App\Entity\Traits\UpdateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

/**
 * Person
 *
 * @ORM\Table(name="person")
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class Person
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="person_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $personId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="firstname", type="string", length=64, nullable=true)
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
     * @ORM\Column(name="photo", type="string", length=256, nullable=true)
     */
    private $photo;

    /**
     * @ORM\OneToMany(targetEntity="PersonAddressLink", mappedBy="person")
     * @SWG\Property(ref=@Model(type=Address::class))
     */
    private $addresses;

    /**
     * @ORM\OneToMany(targetEntity="PersonPhoneLink", mappedBy="person")
     * @SWG\Property(ref=@Model(type=Phone::class))
     */
    private $phones;

    /**
     * @ORM\OneToMany(targetEntity="ChildPersonLink", mappedBy="person")
     * @SWG\Property(ref=@Model(type=Child::class))
     */
    private $children;

    /**
     * @var string|null
     * Not mapped (for documentation), added from PersonService->toArray()
     */
    private $identifier;

    /**
     * @var string|null
     * Not mapped (for documentation), added from PersonService->toArray()
     */
    private $email;

    /**
     * @ORM\OneToOne(targetEntity="UserPersonLink", mappedBy="person")
     */
    private $userPersonLink;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->phones = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        $objectArray = get_object_vars($this);

        return $objectArray;
    }

    public function getPersonId(): ?int
    {
        return $this->personId;
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

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto($photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getAddresses()
    {
        return $this->addresses;
    }

    public function addAddress(PersonAddressLink $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses[] = $address;
            $address->setPerson($this);
        }

        return $this;
    }

    public function removeAddress(PersonAddressLink $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            // set the owning side to null (unless already changed)
            if ($address->getPerson() === $this) {
                $address->setPerson(null);
            }
        }

        return $this;
    }

    public function getPhones()
    {
        return $this->phones;
    }

    public function addPhone(PersonPhoneLink $phone): self
    {
        if (!$this->phones->contains($phone)) {
            $this->phones[] = $phone;
            $phone->setPerson($this);
        }

        return $this;
    }

    public function removePhone(PersonPhoneLink $phone): self
    {
        if ($this->phones->contains($phone)) {
            $this->phones->removeElement($phone);
            // set the owning side to null (unless already changed)
            if ($phone->getPerson() === $this) {
                $phone->setPerson(null);
            }
        }

        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addChild(ChildPersonLink $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setPerson($this);
        }

        return $this;
    }

    public function removeChild(ChildPersonLink $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getPerson() === $this) {
                $child->setPerson(null);
            }
        }

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserPersonLink(): ?UserPersonLink
    {
        return $this->userPersonLink;
    }

    public function setUserPersonLink(?UserPersonLink $userPersonLink): self
    {
        $this->userPersonLink = $userPersonLink;

        return $this;
    }
}

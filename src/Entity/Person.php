<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\SuppressionTrait;

/**
 * Person
 *
 * @ORM\Table(name="person")
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person
{
    use CreationTrait;
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
     * @ORM\OneToMany(targetEntity="PersonAddressLink", mappedBy="person")
     */
    private $addresses;

    /**
     * @ORM\OneToMany(targetEntity="ChildPersonLink", mappedBy="person")
     */
    private $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * Converts the entity in an array
     */
    public function toArray($getAddresses = true, $getChildren = true)
    {
        $personArray = get_object_vars($this);

        //Gets related addresses
        if ($getAddresses) {
            $addresses = array();
            foreach($this->getAddresses()->toArray() as $address) {
                $addresses[] = $address->getAddress()->toArray();
            }
            $personArray['addresses'] = $addresses;
        }

        //Gets related children
        if ($getChildren) {
            $children = array();
            foreach($this->getChildren()->toArray() as $child) {
                $children[] = array(
                    'childId' => $child->getChild()->getChildId(),
                );
            }
            $personArray['children'] = $children;
        }

        return $personArray;
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

    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }
}

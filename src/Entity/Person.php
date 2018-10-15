<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Person
 *
 * @ORM\Table(name="person")
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person
{
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

    public function __construct()
    {
        $this->persons = new ArrayCollection();
    }

    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        $person = get_object_vars($this);
        unset($person['__initializer__']);
        unset($person['__cloner__']);
        unset($person['__isInitialized__']);

        //Gets related addresses
        $addresses = array();
        foreach($this->getAddresses()->toArray() as $address) {
            $addresses[] = $address->getAddress()->toArray();
        }
        $person['addresses'] = $addresses;

        return $person;
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
}

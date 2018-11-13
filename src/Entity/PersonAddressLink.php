<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Address;
use App\Entity\Person;

/**
 * PersonAddressLink
 *
 * @ORM\Table(name="person_address_link", indexes={@ORM\Index(name="person_address_link_address_FK", columns={"address_id"}), @ORM\Index(name="person_address_link_person_FK", columns={"person_id"})})
 * @ORM\Entity
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PersonAddressLink
{
    /**
     * @var int
     *
     * @ORM\Column(name="person_address_link_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $personAddressLinkId;

    /**
     * @var App\Entity\Address
     *
     * @ORM\ManyToOne(targetEntity="Address", inversedBy="persons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="address_id", referencedColumnName="address_id")
     * })
     */
    private $address;

    /**
     * @var App\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="addresses")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="person_id")
     * })
     */
    private $person;

    public function getPersonAddressLinkId(): ?int
    {
        return $this->personAddressLinkId;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

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
}

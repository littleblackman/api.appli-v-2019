<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\UpdateTrait;
use App\Entity\Traits\SuppressionTrait;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

/**
 * Address
 *
 * @ORM\Table(name="address")
 * @ORM\Entity(repositoryClass="App\Repository\AddressRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class Address
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="address_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $addressId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=48, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="address", type="string", length=128, nullable=true)
     */
    private $address;

    /**
     * @var string|null
     *
     * @ORM\Column(name="address2", type="string", length=128, nullable=true)
     */
    private $address2;

    /**
     * @var string|null
     *
     * @ORM\Column(name="postal", type="string", length=10, nullable=true)
     */
    private $postal;

    /**
     * @var string|null
     *
     * @ORM\Column(name="town", type="string", length=64, nullable=true)
     */
    private $town;

    /**
     * @var string|null
     *
     * @ORM\Column(name="country", type="string", length=64, nullable=true)
     */
    private $country;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone", type="string", length=35, nullable=true)
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="PersonAddressLink", mappedBy="address")
     * @SWG\Property(ref=@Model(type=Person::class))
     */
    private $persons;


    public function __construct()
    {
        $this->persons = new ArrayCollection();
    }

    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        $addressArray = get_object_vars($this);

        //Gets related persons
        $persons = array();
        foreach($this->getPersons()->toArray() as $personLink) {
            $persons[] = array(
                'personId' => $personLink->getPerson()->getPersonId(),
            );
        }
        $addressArray['persons'] = $persons;

        return $addressArray;
    }

    public function getAddressId(): ?int
    {
        return $this->addressId;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): self
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getPostal(): ?string
    {
        return $this->postal;
    }

    public function setPostal(?string $postal): self
    {
        $this->postal = $postal;

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(?string $town): self
    {
        $this->town = $town;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

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

    public function getPersons()
    {
        return $this->persons;
    }
}

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
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class Product
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="product_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $productId;

    /**
     * @var Family
     *
     * @ORM\ManyToOne(targetEntity="Family", inversedBy="products")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="family_id", referencedColumnName="family_id")
     * })
     */
    private $family;

    /**
     * @var Season
     *
     * @ORM\OneToOne(targetEntity="Season")
     * @ORM\JoinColumn(name="season_id", referencedColumnName="season_id")
     */
    private $season;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name_fr", type="string", length=128, nullable=true)
     */
    private $nameFr;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name_en", type="string", length=128, nullable=true)
     */
    private $nameEn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description_fr", type="string", nullable=true)
     */
    private $descriptionFr;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description_en", type="string", nullable=true)
     */
    private $descriptionEn;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="date_start", type="date")
     */
    private $dateStart;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="date_end", type="date")
     */
    private $dateEnd;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="exclusion_from", type="date")
     */
    private $exclusionFrom;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="exclusion_to", type="date")
     */
    private $exclusionTo;

    /**
     * @var Location
     *
     * @ORM\OneToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="location_id")
     */
    private $location;

    /**
     * @var boolean
     *
     * @ORM\Column(name="transport", type="boolean")
     */
    private $transport;

    /**
     * @var string|null
     *
     * @ORM\Column(name="day_reference", length=8, type="string")
     */
    private $dayReference;

    /**
     * @var string|null
     *
     * @ORM\Column(name="days_available", length=256, type="string")
     */
    private $daysAvailable;

    /**
     * @var string|null
     *
     * @ORM\Column(name="duration", length=256, type="string")
     */
    private $duration;

    /**
     * @var string|null
     *
     * @ORM\Column(name="expected_times", length=256, type="string")
     */
    private $expectedTimes;

    /**
     * @var string|null
     *
     * @ORM\Column(name="photo", type="string", length=256, nullable=true)
     */
    private $photo;

    /**
     * @ORM\OneToMany(targetEntity="ProductComponentLink", mappedBy="product")
     * @SWG\Property(ref=@Model(type=Component::class))
     */
    private $components;

    /**
     * @var string[]|null
     * "vatAmounts": {"rate": {
     *      "0": {"componentId": 2, "componentName": "name", "vatAmount": 16.67, "ht": 83.33, "price": 100},
     *      "vat": 16.67,
     *      "ht": 83.33,
     *      "ttc": 100
     *      },
     *   }
     */
    private $vatAmounts;

    /**
     * @var float|null
     * @SWG\Property(type="number")
     */
    private $priceTtc;

    public function __construct()
    {
        $this->components = new ArrayCollection();
    }

    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        //Calculates VAT related prices
        $componentLinks = $this->getComponents();
        if (null !== $componentLinks) {
            $vatArray = array();
            $priceTtc = 0;
            foreach ($componentLinks as $componentLink) {
                $component = $componentLink->getComponent();
                $vatRate = $component->getVat();
                $componentVatAmount = round($component->getPrice() - ($component->getPrice() / (1 + ($component->getVat() / 100))), 2, PHP_ROUND_HALF_UP);
                $componentHtAmount = round($component->getPrice() - $componentVatAmount, 2, PHP_ROUND_HALF_UP);
                $vatArray["$vatRate"][] = array(
                    'componentId' => $component->getComponentId(),
                    'componentName' => $component->getNameFr(),
                    'vatAmount' => $componentVatAmount,
                    'ht' => $componentHtAmount,
                    'price' => $component->getPrice(),
                );
                $vatArray["$vatRate"]['vat'] = isset($vatArray["$vatRate"]['vat']) ? $vatArray["$vatRate"]['vat'] + $componentVatAmount : $componentVatAmount;
                $vatArray["$vatRate"]['ht'] = isset($vatArray["$vatRate"]['ht']) ? $vatArray["$vatRate"]['ht'] + $componentHtAmount : $componentHtAmount;
                $vatArray["$vatRate"]['ttc'] = isset($vatArray["$vatRate"]['ttc']) ? $vatArray["$vatRate"]['ttc'] + $component->getPrice() : $component->getPrice();

                $priceTtc += $component->getPrice();
            }
            $this
                ->setVatAmounts($vatArray)
                ->setPriceTtc($priceTtc)
            ;
        }

        //Converts to array
        $objectArray = get_object_vars($this);

        //Specific data
        if (null !== $objectArray['dateStart']) {
            $objectArray['dateStart'] = $objectArray['dateStart']->format('Y-m-d');
        }
        if (null !== $objectArray['dateEnd']) {
            $objectArray['dateEnd'] = $objectArray['dateEnd']->format('Y-m-d');
        }
        if (null !== $objectArray['exclusionFrom']) {
            $objectArray['exclusionFrom'] = $objectArray['exclusionFrom']->format('Y-m-d');
        }
        if (null !== $objectArray['exclusionTo']) {
            $objectArray['exclusionTo'] = $objectArray['exclusionTo']->format('Y-m-d');
        }
        $fieldsArray = array(
            'daysAvailable',
            'duration',
            'expectedTimes',
        );
        foreach ($fieldsArray as $field) {
            $objectArray[$field] = unserialize($objectArray[$field]);
        }

        return $objectArray;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function getFamily(): ?Family
    {
        return $this->family;
    }

    public function setFamily(?Family $family): self
    {
        $this->family = $family;

        return $this;
    }

    public function getNameFr(): ?string
    {
        return $this->nameFr;
    }

    public function setNameFr(?string $nameFr): self
    {
        $this->nameFr = $nameFr;

        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(?string $nameEn): self
    {
        $this->nameEn = $nameEn;

        return $this;
    }

    public function getDescriptionFr(): ?string
    {
        return $this->descriptionFr;
    }

    public function setDescriptionFr(?string $descriptionFr): self
    {
        $this->descriptionFr = $descriptionFr;

        return $this;
    }

    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    public function setDescriptionEn(?string $descriptionEn): self
    {
        $this->descriptionEn = $descriptionEn;

        return $this;
    }

    public function getDateStart(): ?DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(?DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getExclusionFrom(): ?DateTimeInterface
    {
        return $this->exclusionFrom;
    }

    public function setExclusionFrom(?DateTimeInterface $exclusionFrom): self
    {
        $this->exclusionFrom = $exclusionFrom;

        return $this;
    }

    public function getExclusionTo(): ?DateTimeInterface
    {
        return $this->exclusionTo;
    }

    public function setExclusionTo(?DateTimeInterface $exclusionTo): self
    {
        $this->exclusionTo = $exclusionTo;

        return $this;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): self
    {
        $this->season = $season;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getTransport(): ?bool
    {
        return $this->transport;
    }

    public function setTransport(?bool $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function getDayReference(): ?string
    {
        return $this->dayReference;
    }

    public function setDayReference(?string $dayReference): self
    {
        $this->dayReference = $dayReference;

        return $this;
    }

    public function getDaysAvailable(): ?string
    {
        return $this->daysAvailable;
    }

    public function setDaysAvailable(?string $daysAvailable): self
    {
        $this->daysAvailable = $daysAvailable;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getExpectedTimes(): ?string
    {
        return unserialize($this->expectedTimes);
    }

    public function setExpectedTimes(array $expectedTimes): self
    {
        $this->expectedTimes = serialize($expectedTimes);

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

    public function getVatAmounts(): ?array
    {
        return $this->vatAmounts;
    }

    public function setVatAmounts(?array $vatAmounts): self
    {
        $this->vatAmounts = $vatAmounts;

        return $this;
    }

    public function getPriceTtc(): ?float
    {
        return $this->priceTtc;
    }

    public function setPriceTtc(?float $priceTtc): self
    {
        $this->priceTtc = $priceTtc;

        return $this;
    }

    public function getComponents()
    {
        return $this->components;
    }

    public function addComponent(ProductComponentLink $component): self
    {
        if (!$this->components->contains($component)) {
            $this->components[] = $component;
            $component->setProduct($this);
        }

        return $this;
    }

    public function removeComponent(ProductComponentLink $component): self
    {
        if ($this->components->contains($component)) {
            $this->components->removeElement($component);
            // set the owning side to null (unless already changed)
            if ($component->getProduct() === $this) {
                $component->setProduct(null);
            }
        }

        return $this;
    }
}

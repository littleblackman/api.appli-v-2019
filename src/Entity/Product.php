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
     * @var boolean
     *
     * @ORM\Column(name="transport", type="boolean")
     */
    private $transport;

    /**
     * @var string|null
     *
     * @ORM\Column(name="photo", type="string", length=256, nullable=true)
     */
    private $photo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_location_selectable", type="boolean")
     */
    private $isLocationSelectable;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_date_selectable", type="boolean")
     */
    private $isDateSelectable;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_hour_selectable", type="boolean")
     */
    private $isHourSelectable;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_sport_associated", type="boolean")
     */
    private $isSportAssociated;

    /**
     * @var string|null
     *
     * @ORM\Column(name="visibility", type="string", length=32, nullable=true)
     */
    private $visibility;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="hour_dropin", type="time")
     */
    private $hourDropin;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="hour_dropoff", type="time")
     */
    private $hourDropoff;

    /**
     * @ORM\OneToMany(targetEntity="ProductCategoryLink", mappedBy="product")
     * @SWG\Property(ref=@Model(type=Category::class))
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity="ProductComponentLink", mappedBy="product")
     * @SWG\Property(ref=@Model(type=Component::class))
     */
    private $components;

    /**
     * @ORM\OneToMany(targetEntity="ProductDateLink", mappedBy="product")
     */
    private $dates;

    /**
     * @ORM\OneToMany(targetEntity="ProductHourLink", mappedBy="product")
     */
    private $hours;

    /**
     * @ORM\OneToMany(targetEntity="ProductLocationLink", mappedBy="product")
     * @SWG\Property(ref=@Model(type=Location::class))
     */
    private $locations;

    /**
     * @ORM\OneToMany(targetEntity="ProductSportLink", mappedBy="product")
     * @SWG\Property(ref=@Model(type=Sport::class))
     */
    private $sports;

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
        $this->categories = new ArrayCollection();
        $this->components = new ArrayCollection();
        $this->dates = new ArrayCollection();
        $this->hours = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->sports = new ArrayCollection();
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
        if (null !== $objectArray['hourDropin']) {
            $objectArray['hourDropin'] = $objectArray['hourDropin']->format('H:i:s');
        }
        if (null !== $objectArray['hourDropoff']) {
            $objectArray['hourDropoff'] = $objectArray['hourDropoff']->format('H:i:s');
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

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): self
    {
        $this->season = $season;

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

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getIsLocationSelectable(): ?bool
    {
        return $this->isLocationSelectable;
    }

    public function setIsLocationSelectable(bool $isLocationSelectable): self
    {
        $this->isLocationSelectable = $isLocationSelectable;

        return $this;
    }

    public function getIsDateSelectable(): ?bool
    {
        return $this->isDateSelectable;
    }

    public function setIsDateSelectable(bool $isDateSelectable): self
    {
        $this->isDateSelectable = $isDateSelectable;

        return $this;
    }

    public function getIsHourSelectable(): ?bool
    {
        return $this->isHourSelectable;
    }

    public function setIsHourSelectable(bool $isHourSelectable): self
    {
        $this->isHourSelectable = $isHourSelectable;

        return $this;
    }

    public function getIsSportAssociated(): ?bool
    {
        return $this->isSportAssociated;
    }

    public function setIsSportAssociated(bool $isSportAssociated): self
    {
        $this->isSportAssociated = $isSportAssociated;

        return $this;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(?string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getHourDropin(): ?DateTimeInterface
    {
        return $this->hourDropin;
    }

    public function setHourDropin(DateTimeInterface $hourDropin): self
    {
        $this->hourDropin = $hourDropin;

        return $this;
    }

    public function getHourDropoff(): ?DateTimeInterface
    {
        return $this->hourDropoff;
    }

    public function setHourDropoff(DateTimeInterface $hourDropoff): self
    {
        $this->hourDropoff = $hourDropoff;

        return $this;
    }

    /**
     * @return Collection|ProductComponentLink[]
     */
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

    /**
     * @return Collection|ProductCategoryLink[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(ProductCategoryLink $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->setProduct($this);
        }

        return $this;
    }

    public function removeCategory(ProductCategoryLink $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            // set the owning side to null (unless already changed)
            if ($category->getProduct() === $this) {
                $category->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductDateLink[]
     */
    public function getDates(): Collection
    {
        return $this->dates;
    }

    public function addDate(ProductDateLink $date): self
    {
        if (!$this->dates->contains($date)) {
            $this->dates[] = $date;
            $date->setProduct($this);
        }

        return $this;
    }

    public function removeDate(ProductDateLink $date): self
    {
        if ($this->dates->contains($date)) {
            $this->dates->removeElement($date);
            // set the owning side to null (unless already changed)
            if ($date->getProduct() === $this) {
                $date->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductHourLink[]
     */
    public function getHours(): Collection
    {
        return $this->hours;
    }

    public function addHour(ProductHourLink $hour): self
    {
        if (!$this->hours->contains($hour)) {
            $this->hours[] = $hour;
            $hour->setProduct($this);
        }

        return $this;
    }

    public function removeHour(ProductHourLink $hour): self
    {
        if ($this->hours->contains($hour)) {
            $this->hours->removeElement($hour);
            // set the owning side to null (unless already changed)
            if ($hour->getProduct() === $this) {
                $hour->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductLocationLink[]
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(ProductLocationLink $location): self
    {
        if (!$this->locations->contains($location)) {
            $this->locations[] = $location;
            $location->setProduct($this);
        }

        return $this;
    }

    public function removeLocation(ProductLocationLink $location): self
    {
        if ($this->locations->contains($location)) {
            $this->locations->removeElement($location);
            // set the owning side to null (unless already changed)
            if ($location->getProduct() === $this) {
                $location->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductSportLink[]
     */
    public function getSports(): Collection
    {
        return $this->sports;
    }

    public function addSport(ProductSportLink $sport): self
    {
        if (!$this->sports->contains($sport)) {
            $this->sports[] = $sport;
            $sport->setProduct($this);
        }

        return $this;
    }

    public function removeSport(ProductSportLink $sport): self
    {
        if ($this->sports->contains($sport)) {
            $this->sports->removeElement($sport);
            // set the owning side to null (unless already changed)
            if ($sport->getProduct() === $this) {
                $sport->setProduct(null);
            }
        }

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
}

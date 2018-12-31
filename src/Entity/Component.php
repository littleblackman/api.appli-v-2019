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
 * Component
 *
 * @ORM\Table(name="component")
 * @ORM\Entity(repositoryClass="App\Repository\ComponentRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class Component
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="component_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $componentId;

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
     * @var string
     *
     * @ORM\Column(name="description_fr", type="string", nullable=true)
     */
    private $descriptionFr;

    /**
     * @var string
     *
     * @ORM\Column(name="description_en", type="string", nullable=true)
     */
    private $descriptionEn;

    /**
     * @var float|null
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

    /**
     * @var float|null
     *
     * @ORM\Column(name="vat", type="float", nullable=true)
     */
    private $vat;

    /**
     * @var float|null
     * @SWG\Property(type="number")
     */
    private $vatAmount;

    /**
     * @var float|null
     * @SWG\Property(type="number")
     */
    private $priceHt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="photo", type="string", length=256, nullable=true)
     */
    private $photo;

    /**
     * @ORM\OneToMany(targetEntity="ProductComponentLink", mappedBy="component")
     * @SWG\Property(ref=@Model(type=Product::class))
     */
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        //Calculates VAT related prices
        $vatAmount = round($this->getPrice() - ($this->getPrice() / (1 + ($this->getVat() / 100))), 2, PHP_ROUND_HALF_UP);
        $this
            ->setVatAmount($vatAmount)
            ->setPriceHt($this->getPrice() - $vatAmount)
        ;

        $componentArray = get_object_vars($this);

        return $componentArray;
    }

    public function getComponentId(): ?int
    {
        return $this->componentId;
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

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getVat()
    {
        return $this->vat;
    }

    public function setVat($vat): self
    {
        $this->vat = $vat;

        return $this;
    }

    public function getVatAmount()
    {
        return $this->vatAmount;
    }

    public function setVatAmount($vatAmount): self
    {
        $this->vatAmount = $vatAmount;

        return $this;
    }

    public function getPriceHt()
    {
        return $this->priceHt;
    }

    public function setPriceHt($priceHt): self
    {
        $this->priceHt = $priceHt;

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

    public function getProducts()
    {
        return $this->products;
    }

    public function addProduct(ProductComponentLink $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setComponent($this);
        }

        return $this;
    }

    public function removeProduct(ProductComponentLink $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getComponent() === $this) {
                $product->setComponent(null);
            }
        }

        return $this;
    }
}

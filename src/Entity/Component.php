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
     * @ORM\Column(name="name", type="string", length=128, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

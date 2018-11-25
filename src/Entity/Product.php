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
use App\Entity\Component;
use App\Entity\Child;

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
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description;

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

    /**
     * @ORM\OneToMany(targetEntity="ProductComponentLink", mappedBy="product")
     * @SWG\Property(ref=@Model(type=Component::class))
     */
    private $components;

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
                    'componentName' => $component->getName(),
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

        $objectArray = get_object_vars($this);

        return $objectArray;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
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

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getVatAmounts()
    {
        return $this->vatAmounts;
    }

    public function setVatAmounts($vatAmounts): self
    {
        $this->vatAmounts = $vatAmounts;

        return $this;
    }

    public function getPriceTtc()
    {
        return $this->priceTtc;
    }

    public function setPriceTtc($priceTtc): self
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

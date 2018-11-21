<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Component;
use App\Entity\Product;

/**
 * ProductComponentLink
 *
 * @ORM\Table(name="product_component_link", indexes={@ORM\Index(name="product_component_link_component_FK", columns={"component_id"}), @ORM\Index(name="product_component_link_product_FK", columns={"product_id"})})
 * @ORM\Entity
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ProductComponentLink
{
    /**
     * @var int
     *
     * @ORM\Column(name="product_component_link_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $productComponentLinkId;

    /**
     * @var App\Entity\Component
     *
     * @ORM\ManyToOne(targetEntity="Component", inversedBy="products")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="component_id", referencedColumnName="component_id")
     * })
     */
    private $component;

    /**
     * @var App\Entity\Product
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="components")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id", referencedColumnName="product_id")
     * })
     */
    private $product;

    public function getProductComponentLinkId(): ?int
    {
        return $this->productComponentLinkId;
    }

    public function getComponent(): ?Component
    {
        return $this->component;
    }

    public function setComponent(?Component $component): self
    {
        $this->component = $component;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChildProductLink
 *
 * @ORM\Table(name="child_product_link", indexes={@ORM\Index(name="child_product_link_product_FK", columns={"product_id"}), @ORM\Index(name="child_product_link_child_FK", columns={"child_id"})})
 * @ORM\Entity
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ChildProductLink
{
    /**
     * @var int
     *
     * @ORM\Column(name="child_product_link_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $childProductLinkId;

    /**
     * @var \Child
     *
     * @ORM\ManyToOne(targetEntity="Child")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="child_id", referencedColumnName="child_id")
     * })
     */
    private $child;

    /**
     * @var \Product
     *
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id", referencedColumnName="product_id")
     * })
     */
    private $product;

    public function getChildProductLinkId(): ?int
    {
        return $this->childProductLinkId;
    }

    public function getChild(): ?Child
    {
        return $this->child;
    }

    public function setChild(?Child $child): self
    {
        $this->child = $child;

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

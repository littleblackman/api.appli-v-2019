<?php

namespace App\Entity;

use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\SuppressionTrait;
use App\Entity\Traits\UpdateTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * InvoiceComponent
 *
 * @ORM\Table(name="invoice_component", indexes={@ORM\Index(name="invoice_component_component_FK", columns={"component_id"}), @ORM\Index(name="invoice_component_invoice_FK", columns={"invoice_id"})})
 * @ORM\Entity
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class InvoiceComponent
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="invoice_component_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $invoiceComponentId;

    /**
     * @var Invoice
     *
     * @ORM\ManyToOne(targetEntity="InvoiceProduct", inversedBy="invoiceComponents", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="invoice_product_id", referencedColumnName="invoice_product_id")
     * })
     */
    private $invoiceProduct;

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
     * @var float|null
     *
     * @ORM\Column(name="vat", type="float", nullable=true)
     */
    private $vat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="price_ht", type="float", nullable=true)
     */
    private $priceHt;

    /**
     * @var float|null
     *
     * @ORM\Column(name="price_vat", type="float", nullable=true)
     */
    private $priceVat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="price_ttc", type="float", nullable=true)
     */
    private $priceTtc;

    /**
     * @var int|null
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @var float|null
     *
     * @ORM\Column(name="total_ht", type="float", nullable=true)
     */
    private $totalHt;

    /**
     * @var float|null
     *
     * @ORM\Column(name="total_vat", type="float", nullable=true)
     */
    private $totalVat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="total_ttc", type="float", nullable=true)
     */
    private $totalTtc;

    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        $objectArray = get_object_vars($this);

        return $objectArray;
    }

    public function getInvoiceComponentId(): ?int
    {
        return $this->invoiceComponentId;
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

    public function getVat(): ?float
    {
        return $this->vat;
    }

    public function setVat(?float $vat): self
    {
        $this->vat = $vat;

        return $this;
    }

    public function getPriceHt(): ?float
    {
        return $this->priceHt;
    }

    public function setPriceHt(?float $priceHt): self
    {
        $this->priceHt = $priceHt;

        return $this;
    }

    public function getPriceVat(): ?float
    {
        return $this->priceVat;
    }

    public function setPriceVat(?float $priceVat): self
    {
        $this->priceVat = $priceVat;

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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getTotalHt(): ?float
    {
        return $this->totalHt;
    }

    public function setTotalHt(?float $totalHt): self
    {
        $this->totalHt = $totalHt;

        return $this;
    }

    public function getTotalVat(): ?float
    {
        return $this->totalVat;
    }

    public function setTotalVat(?float $totalVat): self
    {
        $this->totalVat = $totalVat;

        return $this;
    }

    public function getTotalTtc(): ?float
    {
        return $this->totalTtc;
    }

    public function setTotalTtc(?float $totalTtc): self
    {
        $this->totalTtc = $totalTtc;

        return $this;
    }

    public function getInvoiceProduct(): ?InvoiceProduct
    {
        return $this->invoiceProduct;
    }

    public function setInvoiceProduct(?InvoiceProduct $invoiceProduct): self
    {
        $this->invoiceProduct = $invoiceProduct;

        return $this;
    }
}

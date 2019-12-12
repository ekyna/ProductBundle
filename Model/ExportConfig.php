<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;

/**
 * Class ExportConfig
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExportConfig
{
    const FORMAT_CSV = 'csv';

    const COLUMN_DESIGNATION = 'designation';
    const COLUMN_REFERENCE   = 'reference';
    const COLUMN_NET_PRICE   = 'net_price';
    const COLUMN_DISCOUNT    = 'discount';
    const COLUMN_BUY_PRICE   = 'quote_price';
    const COLUMN_VALID_UNTIL = 'valid_until';
    const COLUMN_WEIGHT      = 'weight';
    const COLUMN_WIDTH       = 'width';
    const COLUMN_HEIGHT      = 'height';
    const COLUMN_DEPTH       = 'depth';
    const COLUMN_DESCRIPTION = 'description';
    const COLUMN_IMAGE       = 'image';
    const COLUMN_URL         = 'url';

    /**
     * @var string
     */
    private $format;

    /**
     * @var array
     */
    private $columns;

    /**
     * @var Collection|BrandInterface[]
     */
    private $brands;

    /**
     * @var bool
     */
    private $visible;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var \DateTime
     */
    private $validUntil;

    /**
     * @var string
     */
    private $separator = ',';

    /**
     * @var string
     */
    private $enclosure = '"';


    /**
     * Constructor.
     *
     * @param ContextInterface $context
     */
    public function __construct(ContextInterface $context)
    {
        $this->format     = self::FORMAT_CSV;
        $this->columns    = array_values(self::getColumnsChoices());
        $this->brands     = new ArrayCollection();
        $this->visible    = true;
        $this->context    = $context;
        $this->validUntil = new \DateTime('last day of December');
    }

    /**
     * Returns the format.
     *
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Sets the format.
     *
     * @param string $format
     *
     * @return ExportConfig
     */
    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Returns the columns.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Sets the columns.
     *
     * @param array $columns
     *
     * @return ExportConfig
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Returns the brands.
     *
     * @return Collection|BrandInterface[]
     */
    public function getBrands(): Collection
    {
        return $this->brands;
    }

    /**
     * Adds the brand.
     *
     * @param BrandInterface $brand
     *
     * @return ExportConfig
     */
    public function addBrand(BrandInterface $brand): self
    {
        if (!$this->brands->contains($brand)) {
            $this->brands->add($brand);
        }

        return $this;
    }

    /**
     * Removes the brand.
     *
     * @param BrandInterface $brand
     *
     * @return ExportConfig
     */
    public function removeBrand(BrandInterface $brand): self
    {
        if ($this->brands->contains($brand)) {
            $this->brands->removeElement($brand);
        }

        return $this;
    }

    /**
     * Returns the visible.
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Sets the visible.
     *
     * @param bool $visible
     *
     * @return ExportConfig
     */
    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Returns the context.
     *
     * @return ContextInterface
     */
    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    /**
     * Returns the valid until.
     *
     * @return \DateTime
     */
    public function getValidUntil(): ?\DateTime
    {
        return $this->validUntil;
    }

    /**
     * Sets the valid until.
     *
     * @param \DateTime $date
     *
     * @return ExportConfig
     */
    public function setValidUntil(\DateTime $date): ExportConfig
    {
        $this->validUntil = $date;

        return $this;
    }

    /**
     * Returns the separator.
     *
     * @return string
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
     * Sets the separator.
     *
     * @param string $separator
     *
     * @return ExportConfig
     */
    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Returns the enclosure.
     *
     * @return string
     */
    public function getEnclosure(): string
    {
        return $this->enclosure;
    }

    /**
     * Sets the enclosure.
     *
     * @param string $enclosure
     *
     * @return ExportConfig
     */
    public function setEnclosure(string $enclosure): self
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * Returns the columns choices (for form type).
     *
     * @return array
     */
    public static function getColumnsChoices(): array
    {
        return [
            'ekyna_core.field.designation'            => self::COLUMN_DESIGNATION,
            'ekyna_core.field.reference'              => self::COLUMN_REFERENCE,
            'ekyna_product.export.column.net_price'   => self::COLUMN_NET_PRICE,
            'ekyna_commerce.sale.field.discount'      => self::COLUMN_DISCOUNT,
            'ekyna_product.export.column.buy_price'   => self::COLUMN_BUY_PRICE,
            'ekyna_product.export.column.valid_until' => self::COLUMN_VALID_UNTIL,
            'ekyna_core.field.weight'                 => self::COLUMN_WEIGHT,
            'ekyna_core.field.width'                  => self::COLUMN_WIDTH,
            'ekyna_core.field.height'                 => self::COLUMN_HEIGHT,
            'ekyna_core.field.depth'                  => self::COLUMN_DEPTH,
            'ekyna_core.field.description'            => self::COLUMN_DESCRIPTION,
            'ekyna_core.field.image'                  => self::COLUMN_IMAGE,
            'ekyna_core.field.url'                    => self::COLUMN_URL,
        ];
    }

    /**
     * Returns the format choices (for form type).
     *
     * @return array
     */
    public static function getFormatChoices(): array
    {
        return [
            'CSV' => self::FORMAT_CSV,
        ];
    }
}

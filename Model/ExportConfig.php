<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

use function array_keys;
use function Symfony\Component\Translation\t;

/**
 * Class ExportConfig
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExportConfig
{
    public const FORMAT_CSV = 'csv';

    public const COLUMN_DESIGNATION      = 'designation';
    public const COLUMN_REFERENCE        = 'reference';
    public const COLUMN_EXT_EAN8         = 'ext_ean8';
    public const COLUMN_EXT_EAN13        = 'ext_ean13';
    public const COLUMN_EXT_MANUFACTURER = 'ext_manufacturer';
    public const COLUMN_NET_PRICE        = 'net_price';
    public const COLUMN_DISCOUNT         = 'discount';
    public const COLUMN_SELL_PRICE       = 'quote_price';
    public const COLUMN_BUY_PRICE        = 'buy_price';
    public const COLUMN_BUY_PRICE_SHIP   = 'buy_price_ship';
    public const COLUMN_VALID_UNTIL      = 'valid_until';
    public const COLUMN_WEIGHT           = 'weight';
    public const COLUMN_WIDTH            = 'width';
    public const COLUMN_HEIGHT           = 'height';
    public const COLUMN_DEPTH            = 'depth';
    public const COLUMN_DESCRIPTION      = 'description';
    public const COLUMN_IMAGE            = 'image';
    public const COLUMN_URL              = 'url';

    private string            $format    = self::FORMAT_CSV;
    private array             $columns;
    private bool              $visible   = true;
    private DateTimeInterface $validUntil;
    private string            $separator = ',';
    private string            $enclosure = '"';
    /** @var Collection<int, BrandInterface> */
    private Collection $brands;

    public function __construct(private readonly ContextInterface $context)
    {
        $this->columns = [
            self::COLUMN_DESIGNATION,
            self::COLUMN_REFERENCE,
            self::COLUMN_EXT_EAN13,
            self::COLUMN_NET_PRICE,
            self::COLUMN_DISCOUNT,
            self::COLUMN_SELL_PRICE,
            self::COLUMN_VALID_UNTIL,
            self::COLUMN_IMAGE,
            self::COLUMN_URL,
        ];
        $this->brands = new ArrayCollection();
        $this->validUntil = new DateTime('last day of December');
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return Collection<int, BrandInterface>
     */
    public function getBrands(): Collection
    {
        return $this->brands;
    }

    public function addBrand(BrandInterface $brand): self
    {
        if (!$this->brands->contains($brand)) {
            $this->brands->add($brand);
        }

        return $this;
    }

    public function removeBrand(BrandInterface $brand): self
    {
        if ($this->brands->contains($brand)) {
            $this->brands->removeElement($brand);
        }

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    public function getValidUntil(): DateTimeInterface
    {
        return $this->validUntil;
    }

    public function setValidUntil(DateTimeInterface $date): ExportConfig
    {
        $this->validUntil = $date;

        return $this;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    public function getEnclosure(): string
    {
        return $this->enclosure;
    }

    public function setEnclosure(string $enclosure): self
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * Returns the columns labels (for form type).
     *
     * @return array<string, TranslatableInterface>
     */
    public static function getColumnsLabels(): array
    {
        return [
            self::COLUMN_DESIGNATION      => t('field.designation', [], 'EkynaUi'),
            self::COLUMN_REFERENCE        => t('field.reference', [], 'EkynaUi'),
            self::COLUMN_EXT_EAN8         => t('product_reference.type.ean8', [], 'EkynaProduct'),
            self::COLUMN_EXT_EAN13        => t('product_reference.type.ean13', [], 'EkynaProduct'),
            self::COLUMN_EXT_MANUFACTURER => t('product_reference.type.manufacturer', [], 'EkynaProduct'),
            self::COLUMN_NET_PRICE        => t('export.column.net_price', [], 'EkynaProduct'),
            self::COLUMN_DISCOUNT         => t('sale.field.discount', [], 'EkynaCommerce'),
            self::COLUMN_SELL_PRICE       => t('export.column.sell_price', [], 'EkynaProduct'),
            self::COLUMN_BUY_PRICE        => t('field.buy_net_price', [], 'EkynaCommerce'),
            self::COLUMN_BUY_PRICE_SHIP   => t('field.buy_net_price_including_shipping', [], 'EkynaCommerce'),
            self::COLUMN_VALID_UNTIL      => t('export.column.valid_until', [], 'EkynaProduct'),
            self::COLUMN_WEIGHT           => t('field.weight', [], 'EkynaUi'),
            self::COLUMN_WIDTH            => t('field.width', [], 'EkynaUi'),
            self::COLUMN_HEIGHT           => t('field.height', [], 'EkynaUi'),
            self::COLUMN_DEPTH            => t('field.depth', [], 'EkynaUi'),
            self::COLUMN_DESCRIPTION      => t('field.description', [], 'EkynaUi'),
            self::COLUMN_IMAGE            => t('field.image', [], 'EkynaUi'),
            self::COLUMN_URL              => t('field.url', [], 'EkynaUi'),
        ];
    }

    /**
     * Returns the columns choices.
     *
     * @return array<string>
     */
    public static function getColumnsChoices(): array
    {
        return array_keys(self::getColumnsLabels());
    }

    /**
     * Returns the format labels (for form type).
     *
     * @return array<string, string>
     */
    public static function getFormatLabels(): array
    {
        return [
            self::FORMAT_CSV => 'CSV',
        ];
    }

    /**
     * Returns the format choices.
     *
     * @return array<string>
     */
    public static function getFormatChoices(): array
    {
        return [
            self::FORMAT_CSV,
        ];
    }
}

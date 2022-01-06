<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\OfferInterface;
use Ekyna\Bundle\ProductBundle\Model\PriceInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Class Price
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Price implements PriceInterface
{
    private ?int                    $id           = null;
    private bool                    $startingFrom = false;
    private Decimal                 $originalPrice;
    private Decimal                 $sellPrice;
    private Decimal                 $percent;
    /** @var array<string, string>  */
    private array                   $details      = [];
    private ?DateTimeInterface      $endsAt       = null;
    private ?ProductInterface       $product      = null;
    private ?CustomerGroupInterface $group        = null;
    private ?CountryInterface       $country      = null;

    public function __construct()
    {
        $this->originalPrice = new Decimal(0);
        $this->sellPrice = new Decimal(0);
        $this->percent = new Decimal(0);
    }

    public function __clone()
    {
        $this->id = null;
        $this->originalPrice = clone $this->originalPrice;
        $this->sellPrice = clone $this->sellPrice;
        $this->percent = clone $this->percent;
        $this->product = null;
        $this->country = null;
        $this->group = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isStartingFrom(): bool
    {
        return $this->startingFrom;
    }

    public function setStartingFrom(bool $from): PriceInterface
    {
        $this->startingFrom = $from;

        return $this;
    }

    public function getOriginalPrice(): Decimal
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice(Decimal $price): PriceInterface
    {
        $this->originalPrice = $price;

        return $this;
    }

    public function getSellPrice(): Decimal
    {
        return $this->sellPrice;
    }

    public function setSellPrice(Decimal $price): PriceInterface
    {
        $this->sellPrice = $price;

        return $this;
    }

    public function getPercent(): Decimal
    {
        return $this->percent;
    }

    public function setPercent(Decimal $percent): PriceInterface
    {
        $this->percent = $percent;

        return $this;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function setDetails(array $details): PriceInterface
    {
        $this->details = [];

        foreach ($details as $type => $percent) {
            $this->addDetails($type, $percent);
        }

        return $this;
    }

    public function addDetails(string $type, Decimal $percent): PriceInterface
    {
        if (!in_array($type, [OfferInterface::TYPE_PRICING, OfferInterface::TYPE_SPECIAL], true)) {
            throw new InvalidArgumentException('Unexpected offer type');
        }

        $this->details[$type] = $percent->toFixed(5);

        return $this;
    }

    public function getEndsAt(): ?DateTimeInterface
    {
        return $this->endsAt;
    }

    public function setEndsAt(?DateTimeInterface $endsAt): PriceInterface
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): PriceInterface
    {
        $this->product = $product;

        return $this;
    }

    public function getGroup(): ?CustomerGroupInterface
    {
        return $this->group;
    }

    public function setGroup(?CustomerGroupInterface $group): PriceInterface
    {
        $this->group = $group;

        return $this;
    }

    public function getCountry(): ?CountryInterface
    {
        return $this->country;
    }

    public function setCountry(?CountryInterface $country): PriceInterface
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getDetailedPercents(): array
    {
        $percents = [];

        foreach ([OfferInterface::TYPE_SPECIAL, OfferInterface::TYPE_PRICING] as $type) {
            if (isset($this->details[$type])) {
                $percents[] = $this->details[$type];
            }
        }

        return $percents;
    }
}

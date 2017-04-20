<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing\Config;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model\OfferInterface;

/**
 * Class Result
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Result
{
    protected string  $key;
    protected bool    $startingFrom = false;
    protected Decimal $originalPrice;
    protected Decimal $basePrice;
    protected Decimal $sellPrice;
    /** @var array<string, Decimal> */
    protected array              $discounts;
    protected ?DateTimeInterface $endsAt = null;

    public function __construct(string $key)
    {
        $this->key = $key;

        $this->startingFrom = false;
        $this->originalPrice = new Decimal(0);
        $this->basePrice = new Decimal(0);
        $this->sellPrice = new Decimal(0);

        $this->discounts = [
            OfferInterface::TYPE_SPECIAL => new Decimal(0),
            OfferInterface::TYPE_PRICING => new Decimal(0),
        ];
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setStartingFrom(bool $from): self
    {
        $this->startingFrom = $from;

        return $this;
    }

    public function isStartingFrom(): bool
    {
        return $this->startingFrom;
    }

    public function addOriginalPrice(Decimal $amount): self
    {
        $this->originalPrice += $amount;

        return $this;
    }

    public function getOriginalPrice(): Decimal
    {
        return $this->originalPrice;
    }

    public function addBasePrice(Decimal $amount): self
    {
        $this->basePrice += $amount;

        return $this;
    }

    public function getBasePrice(): Decimal
    {
        return $this->basePrice;
    }

    public function addSellPrice(Decimal $amount): self
    {
        $this->sellPrice += $amount;

        return $this;
    }

    public function getSellPrice(): Decimal
    {
        return $this->sellPrice;
    }

    /**
     * Adds the discount amount for the given type.
     */
    public function addDiscount(string $type, Decimal $amount): self
    {
        $this->discounts[$type] += $amount;

        return $this;
    }

    /**
     * Returns the discount amount for the given type.
     */
    public function getDiscount(string $type): Decimal
    {
        return $this->discounts[$type];
    }

    public function addEndsAt(DateTimeInterface $date): self
    {
        if (is_null($this->endsAt) || $date < $this->endsAt) {
            $this->endsAt = $date;
        }

        return $this;
    }

    public function getEndsAt(): ?DateTimeInterface
    {
        return $this->endsAt;
    }

    public function toArray(): ?array
    {
        // Build price for this group/country couple
        [$group, $country] = explode('-', $this->key);
        if (0 === $group = intval($group)) {
            $group = null;
        }
        if (0 === $country = intval($country)) {
            $country = null;
        }

        $details = [];
        $base = $this->originalPrice;
        foreach ([OfferInterface::TYPE_SPECIAL, OfferInterface::TYPE_PRICING] as $type) {
            if ($this->discounts[$type]->isZero()) {
                continue;
            }

            $discount = $this->discounts[$type];
            $details[$type] = (new Decimal($discount * 100 / $base))->round(5);
            $base -= $discount;
        }

        $percent = new Decimal(0);
        if (0 < $this->originalPrice) {
            $percent = (new Decimal(($this->originalPrice - $this->sellPrice) * 100 / $this->originalPrice))->round(2);
        }

        if (0 < $percent) {
            return [
                'details'        => $details,
                'starting_from'  => $this->startingFrom,
                'original_price' => $this->originalPrice,
                'sell_price'     => $this->sellPrice,
                'percent'        => $percent,
                'group_id'       => $group,
                'country_id'     => $country,
                'ends_at'        => $this->endsAt ? $this->endsAt->format('Y-m-d') : null,
            ];
        }

        return null;
    }
}

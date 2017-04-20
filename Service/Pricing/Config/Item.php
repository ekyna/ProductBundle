<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing\Config;

use Decimal\Decimal;

/**
 * Class Item
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Item
{
    protected Decimal $netPrice;
    protected Decimal $quantity;
    protected bool    $visible = true;
    protected array   $offers  = [];

    public function __construct(Decimal $netPrice = null, Decimal $quantity = null)
    {
        $this->netPrice = $netPrice ?: new Decimal(0);
        $this->quantity = $quantity ?: new Decimal(1);
    }

    public function getNetPrice(): Decimal
    {
        return $this->netPrice;
    }

    public function setNetPrice(Decimal $price): self
    {
        $this->netPrice = $price;

        return $this;
    }

    public function addNetPrice(Decimal $price): self
    {
        $this->netPrice += $price;

        return $this;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }

    public function setQuantity(Decimal $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function getOffers(): array
    {
        return $this->offers;
    }

    public function getOffer(string $key): ?array
    {
        [$group, $country] = explode('-', $key);

        $keys = array_unique([$key, '0-' . $country, $group . '-0', '0-0']);

        foreach ($keys as $k) {
            if (isset($this->offers[$k])) {
                return $this->offers[$k];
            }
        }

        return null;
    }

    public function setOffers(array $offers): self
    {
        $this->offers = $offers;

        return $this;
    }
}

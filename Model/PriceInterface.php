<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface PriceInterface
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PriceInterface extends ResourceInterface
{
    /**
     * Returns whether this price is "starting from".
     */
    public function isStartingFrom(): bool;

    /**
     * Sets whether this price is "starting from".
     */
    public function setStartingFrom(bool $from): PriceInterface;

    public function getOriginalPrice(): Decimal;

    public function setOriginalPrice(Decimal $price): PriceInterface;

    public function getSellPrice(): Decimal;

    public function setSellPrice(Decimal $price): PriceInterface;

    public function getPercent(): Decimal;

    public function setPercent(Decimal $percent): PriceInterface;

    /**
     * @return array<string, string>
     */
    public function getDetails(): array;

    /**
     * @param array<string, Decimal> $details
     */
    public function setDetails(array $details): PriceInterface;

    public function addDetails(string $type, Decimal $percent): PriceInterface;

    public function getEndsAt(): ?DateTimeInterface;

    public function setEndsAt(?DateTimeInterface $endsAt): PriceInterface;

    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): PriceInterface;

    public function getGroup(): ?CustomerGroupInterface;

    public function setGroup(?CustomerGroupInterface $group): PriceInterface;

    public function getCountry(): ?CountryInterface;

    public function setCountry(?CountryInterface $country): PriceInterface;

    public function getDetailedPercents(): array;
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface OfferInterface
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OfferInterface extends ResourceInterface
{
    public const TYPE_SPECIAL = 'special';
    public const TYPE_PRICING = 'pricing';

    public function getMinQuantity(): Decimal;

    public function setMinQuantity(Decimal $quantity): OfferInterface;

    public function getPercent(): Decimal;

    public function setPercent(Decimal $percent): OfferInterface;

    public function getNetPrice(): Decimal;

    public function setNetPrice(Decimal $amount): OfferInterface;

    /**
     * @return array<string, string>
     */
    public function getDetails(): array;

    /**
     * @param array<string, Decimal> $details
     */
    public function setDetails(array $details): OfferInterface;

    public function addDetails(string $type, Decimal $percent): OfferInterface;

    public function getProduct(): ?Model\ProductInterface;

    public function setProduct(?Model\ProductInterface $product): OfferInterface;

    public function getGroup(): ?CustomerGroupInterface;

    public function setGroup(?CustomerGroupInterface $group): OfferInterface;

    public function getCountry(): ?CountryInterface;

    public function setCountry(?CountryInterface $country): OfferInterface;

    public function getSpecialOffer(): ?Model\SpecialOfferInterface;

    public function setSpecialOffer(?Model\SpecialOfferInterface $specialOffer): OfferInterface;

    public function getPricing(): ?Model\PricingInterface;

    public function setPricing(?Model\PricingInterface $pricing): OfferInterface;

    /**
     * Returns the detailed percentages.
     *
     * @return array<Decimal>
     */
    public function getDetailedPercents(): array;
}

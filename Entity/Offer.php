<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\OfferInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Class Offer
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Offer implements OfferInterface
{
    private ?int                         $id           = null;
    private Decimal                      $minQuantity;
    private Decimal                      $percent;
    private Decimal                      $netPrice;
    /** @var array<string, string> */
    private array                        $details      = [];
    private ?Model\ProductInterface      $product      = null;
    private ?CustomerGroupInterface      $group        = null;
    private ?CountryInterface            $country      = null;
    private ?Model\SpecialOfferInterface $specialOffer = null;
    private ?Model\PricingInterface      $pricing      = null;

    public function __construct()
    {
        $this->minQuantity = new Decimal(0);
        $this->percent = new Decimal(0);
        $this->netPrice = new Decimal(0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMinQuantity(): Decimal
    {
        return $this->minQuantity;
    }

    public function setMinQuantity(Decimal $quantity): OfferInterface
    {
        $this->minQuantity = $quantity;

        return $this;
    }

    public function getPercent(): Decimal
    {
        return $this->percent;
    }

    public function setPercent(Decimal $percent): OfferInterface
    {
        $this->percent = $percent;

        return $this;
    }

    public function getNetPrice(): Decimal
    {
        return $this->netPrice;
    }

    public function setNetPrice(Decimal $amount): OfferInterface
    {
        $this->netPrice = $amount;

        return $this;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function setDetails(array $details): OfferInterface
    {
        foreach ($details as $type => $percent) {
            $this->addDetails($type, $percent);
        }

        return $this;
    }

    public function addDetails(string $type, Decimal $percent): OfferInterface
    {
        if (!in_array($type, [OfferInterface::TYPE_PRICING, OfferInterface::TYPE_SPECIAL], true)) {
            throw new InvalidArgumentException('Unexpected offer type');
        }

        $this->details[$type] = $percent->toFixed(2);

        return $this;
    }

    public function getProduct(): ?Model\ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?Model\ProductInterface $product): OfferInterface
    {
        $this->product = $product;

        return $this;
    }

    public function getGroup(): ?CustomerGroupInterface
    {
        return $this->group;
    }

    public function setGroup(?CustomerGroupInterface $group): OfferInterface
    {
        $this->group = $group;

        return $this;
    }

    public function getCountry(): ?CountryInterface
    {
        return $this->country;
    }

    public function setCountry(?CountryInterface $country): OfferInterface
    {
        $this->country = $country;

        return $this;
    }

    public function getSpecialOffer(): ?Model\SpecialOfferInterface
    {
        return $this->specialOffer;
    }

    public function setSpecialOffer(?Model\SpecialOfferInterface $specialOffer): OfferInterface
    {
        $this->specialOffer = $specialOffer;

        return $this;
    }

    public function getPricing(): ?Model\PricingInterface
    {
        return $this->pricing;
    }

    public function setPricing(?Model\PricingInterface $pricing): OfferInterface
    {
        $this->pricing = $pricing;

        return $this;
    }

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

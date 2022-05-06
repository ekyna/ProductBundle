<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Generator;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Exception\LogicException;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Bundle\ProductBundle\Model\PricingInterface;
use Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

use function array_map;
use function implode;
use function sprintf;

/**
 * Class PricingNameGenerator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PricingNameGenerator
{
    public function generatePricingName(PricingInterface $pricing): string
    {
        if (null !== $pricing->getProduct()) {
            throw new LogicException('Pricing for a single product does not need to be named.');
        }

        $parts = [];

        $this->addCustomerGroupsPart($pricing->getGroups(), $parts);

        $this->addBrandsPart($pricing->getBrands(), $parts);

        $this->addCountriesPart($pricing->getCountries(), $parts);

        if (empty($parts)) {
            return 'New pricing';
        }

        return implode(' - ', $parts);
    }

    public function generateSpecialOfferName(SpecialOfferInterface $specialOffer): string
    {
        if (null !== $specialOffer->getProduct()) {
            throw new LogicException('Special offer for a single product does not need to be named.');
        }

        $parts = ['-' . $specialOffer->getPercent()->toFixed(2) . '%'];

        $this->addCustomerGroupsPart($specialOffer->getGroups(), $parts);

        if ($specialOffer->getBrands()->isEmpty()) {
            $parts[] = $specialOffer->getProducts()->count() . ' product(s)';
        } else {
            $this->addBrandsPart($specialOffer->getBrands(), $parts);
        }

        $this->addCountriesPart($specialOffer->getCountries(), $parts);

        if (empty($parts)) {
            return 'New special offer';
        }

        return implode(' - ', $parts);
    }

    protected function addBrandsPart(Collection $brands, array &$parts): void
    {
        if ($brands->isEmpty()) {
            return;
        }

        $part = implode('/', array_map(function (BrandInterface $brand) {
            return $brand->getName();
        }, $brands->slice(0, 3)));

        if (3 < $count = $brands->count()) {
            $part .= sprintf('(+%d)', $count - 3);
        }

        $parts[] = $part;
    }

    protected function addCustomerGroupsPart(Collection $groups, array &$parts): void
    {
        if ($groups->isEmpty()) {
            return;
        }

        $part = implode('/', array_map(function (CustomerGroupInterface $group) {
            return $group->getName();
        }, $groups->slice(0, 3)));

        if (3 < $count = $groups->count()) {
            $part .= sprintf('(+%d)', $count - 3);
        }

        $parts[] = $part;
    }

    protected function addCountriesPart(Collection $countries, array &$parts): void
    {
        if ($countries->isEmpty()) {
            return;
        }

        $part = implode('/', array_map(function (CountryInterface $country) {
            return $country->getName();
        }, $countries->slice(0, 3)));

        if (3 < $count = $countries->count()) {
            $part .= sprintf('(+%d)', $count - 3);
        }

        $parts[] = $part;
    }
}

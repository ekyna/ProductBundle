<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Entity\Offer;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\PricingRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\SpecialOfferRepositoryInterface;

/**
 * Class OfferResolver
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferResolver
{
    /**
     * @var PricingRepositoryInterface
     */
    protected $pricingRepository;

    /**
     * @var SpecialOfferRepositoryInterface
     */
    protected $specialOfferRepository;

    /**
     * @var PriceCalculator
     */
    protected $priceCalculator;


    /**
     * Constructor.
     *
     * @param PricingRepositoryInterface      $pricingRepository
     * @param SpecialOfferRepositoryInterface $specialOfferRepository
     * @param PriceCalculator                 $priceCalculator
     */
    public function __construct(
        PricingRepositoryInterface $pricingRepository,
        SpecialOfferRepositoryInterface $specialOfferRepository,
        PriceCalculator $priceCalculator
    ) {
        $this->pricingRepository      = $pricingRepository;
        $this->specialOfferRepository = $specialOfferRepository;
        $this->priceCalculator        = $priceCalculator;
    }

    /**
     * Resolves the products's offers.
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    public function resolve(ProductInterface $product)
    {
        // Pricing rules
        $discounts = $this->pricingRepository->findRulesByProduct($product);
        foreach ($discounts as &$discount) {
            $discount['details'] = [Offer::TYPE_PRICING => $discount['percent']];
        }

        // Special offers rules
        $specialOffers = $this->specialOfferRepository->findRulesByProduct($product);
        foreach ($specialOffers as &$specialOffer) {
            $specialOffer['details'] = [Offer::TYPE_SPECIAL => $specialOffer['percent']];
        }

        // Stacking special offers rules
        $stackingOffers = array_filter($specialOffers, function ($o) {
            return $o['stack'];
        });

        // Removes worst stacking special offers
        rules_purge($stackingOffers);

        // Apply stacking special offers to pricing rules
        foreach ($stackingOffers as $stacking) {
            foreach ($discounts as &$discount) {
                if (rule_apply_to($stacking, $discount)) {
                    $percent = (1 - (1 - $stacking['percent'] / 100) * (1 - $discount['percent'] / 100)) * 100;

                    $discount['percent']                      = round($percent, 5);
                    $discount['special_offer_id']             = $stacking['special_offer_id'];
                    $discount['details'][Offer::TYPE_SPECIAL] = $stacking['percent'];
                }
            }
        }

        $offers = array_merge($discounts, $specialOffers);

        // Remove worst offers
        rules_purge($offers);

        // Sort results
        usort($offers, __NAMESPACE__ . '\rule_sort');

        // Set net prices
        $netPrice = $product->getMinPrice();
        foreach ($offers as &$data) {
            unset($data['stack']);
            $data['net_price'] = round($netPrice * (1 - $data['percent'] / 100), 5);
        }

        return $offers;
    }
}

/**
 * Removes the worst offers.
 *
 * @param array $rules
 */
function rules_purge(array &$rules): void
{
    foreach ($rules as $ak => $ad) {
        foreach ($rules as $bk => $bd) {
            if ($ak == $bk) {
                continue;
            }

            if (rule_is_better($bd, $ad)) {
                // B is a better rule, remove A
                unset($rules[$ak]);
            }
        }
    }
}

/**
 * Returns whether $a is better than $b.
 *
 * @param array $a
 * @param array $b
 *
 * @return bool
 */
function rule_is_better(array $a, array $b): bool
{
    return rule_apply_to($a, $b) && $a['percent'] >= $b['percent'];
}

/**
 * Returns whether $a is applies to $b.
 *
 * @param array $a
 * @param array $b
 *
 * @return bool
 */
function rule_apply_to(array $a, array $b): bool
{
    return (is_null($a['group_id']) || $a['group_id'] == $b['group_id'])
        && (is_null($a['country_id']) || $a['country_id'] == $b['country_id'])
        && $a['min_qty'] <= $b['min_qty'];
}

/**
 * Sorting comparison function.
 *
 * @param array $a
 * @param array $b
 *
 * @return int
 */
function rule_sort(array $a, array $b): int
{
    if ($a['group_id'] == $b['group_id']) {
        if ($a['country_id'] == $b['country_id']) {
            if ($a['min_qty'] == $b['min_qty']) {
                if ($a['percent'] == $b['percent']) {
                    return 0;
                } else {
                    return $a['percent'] > $b['percent'] ? -1 : 1;
                }
            } else {
                return $a['min_qty'] > $b['min_qty'] ? -1 : 1;
            }
        } else {
            return $a['country_id'] > $b['country_id'] ? -1 : 1;
        }
    } else {
        return $a['group_id'] > $b['group_id'] ? -1 : 1;
    }
}

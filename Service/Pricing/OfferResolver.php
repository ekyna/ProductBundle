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
     * Constructor.
     *
     * @param PricingRepositoryInterface      $pricingRepository
     * @param SpecialOfferRepositoryInterface $specialOfferRepository
     */
    public function __construct(
        PricingRepositoryInterface $pricingRepository,
        SpecialOfferRepositoryInterface $specialOfferRepository
    ) {
        $this->pricingRepository = $pricingRepository;
        $this->specialOfferRepository = $specialOfferRepository;
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
        $discounts = $this->pricingRepository->findRulesByBrand($product->getBrand());
        foreach ($discounts as &$discount) {
            $discount['type'] = Offer::TYPE_PRICING;
        }

        $specialOffers = $this->specialOfferRepository->findRulesByProduct($product);
        foreach ($specialOffers as &$specialOffer) {
            $specialOffer['type'] = Offer::TYPE_SPECIAL;
        }

        $offers = array_merge($discounts, $specialOffers);

        // Purge useless (there is better) rules
        foreach ($offers as $ak => $ad) {
            foreach ($offers as $bk => $bd) {
                if ($ak == $bk) {
                    continue;
                }

                if (rule_is_better($bd, $ad)) {
                    // B is a better rule, remove A
                    unset($offers[$ak]);
                }
            }
        }

        // Sort results
        usort($offers, __NAMESPACE__ . '\rule_sort');

        // Set net prices
        foreach ($offers as &$data) {
            $data['net_price'] = round($product->getNetPrice() * (1 - $data['percent'] / 100), 5);
        }

        return $offers;
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
function rule_is_better(array $a, array $b)
{
    return (is_null($a['group_id']) || $a['group_id'] == $b['group_id'])
        && (is_null($a['country_id']) || $a['country_id'] == $b['country_id'])
        && $a['min_qty'] <= $b['min_qty']
        && $a['percent'] >= $b['percent'];
}

/**
 * Sorting comparison function.
 *
 * @param array $a
 * @param array $b
 *
 * @return int
 */
function rule_sort(array $a, array $b)
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

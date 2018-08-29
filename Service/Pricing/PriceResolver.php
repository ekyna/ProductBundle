<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\OfferRepository;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentData;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;

/**
 * Class PriceResolver
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceResolver
{
    /**
     * @var OfferRepository
     */
    protected $offerRepository;


    /**
     * Constructor.
     *
     * @param OfferRepository $offerRepository
     */
    public function __construct(OfferRepository $offerRepository)
    {
        $this->offerRepository = $offerRepository;
    }

    /**
     * Resolves the product price.
     *
     * @param ProductInterface $product
     * @param ContextInterface $context
     * @param float            $quantity
     * @param bool             $useCache
     *
     * @return \Ekyna\Component\Commerce\Common\Model\AdjustmentData|null
     */
    public function resolve(ProductInterface $product, ContextInterface $context, $quantity = 1.0, $useCache = true)
    {
        $offer = $this
            ->offerRepository
            ->findByProductAndContextAndQuantity($product, $context, $quantity, $useCache);

        if (is_null($offer)) {
            return null;
        }

        return new AdjustmentData(
            AdjustmentModes::MODE_PERCENT,
            sprintf('%s %s%%', 'Reduction', $offer['percent']), // TODO designation
            // TODO translation / number_format
            $offer['percent']
        );
    }

    /**
     * Finds the pricing matching the given product, group and country.
     *
     * @param ProductInterface $product
     * @param ContextInterface $context ,
     * @param bool             $useCache
     *
     * @return array
     */
    public function findPricing(ProductInterface $product, ContextInterface $context, $useCache = true)
    {
        return $this
            ->offerRepository
            ->findByProductAndContext($product, $context, $useCache);
    }
}

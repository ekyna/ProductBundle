<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\PricingRepositoryInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentData;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Class PriceResolver
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceResolver
{
    /**
     * @var PricingRepositoryInterface
     */
    protected $pricingRepository;

    /**
     * @var array
     *
     * [
     *     '{hash}' => [
     *         'designation' => '{designation}',
     *         'rules' => [
     *             {min_quantity} => {percent}
     *         ],
     *     ],
     * ]
     *
     * {hash} = "{group.id}-{country.id}-{brand.id}"
     *
     * sorted by :
     *    group.id     ASC
     *    country.id   ASC
     *    brand.id     ASC
     *    min_quantity DESC
     */
    protected $grid;


    /**
     * Constructor.
     *
     * @param PricingRepositoryInterface       $pricingRepository
     */
    public function __construct(PricingRepositoryInterface $pricingRepository)
    {
        $this->pricingRepository = $pricingRepository;

        $this->initialize();
    }

    /**
     * Clears the grid data.
     */
    public function clear()
    {
        $this->grid = null;
    }

    /**
     * Initializes the price resolver.
     */
    public function initialize()
    {
        if (null === $this->grid) {
            $this->grid = $this->pricingRepository->getGrid();
        }
    }

    /**
     * Resolves the product price.
     *
     * @param ProductInterface $product
     * @param ContextInterface $context
     * @param int              $quantity
     *
     * @return \Ekyna\Component\Commerce\Common\Model\AdjustmentData|null
     */
    public function resolve(
        ProductInterface $product,
        ContextInterface $context,
        $quantity
    ) {
        $pricing = $this->findPricing($product, $context);

        if (!empty($pricing)) {
            foreach ($pricing['rules'] as $rule) {
                if ($rule['quantity'] <= $quantity) {
                    return new AdjustmentData(
                        AdjustmentModes::MODE_PERCENT,
                        sprintf('%s %s%%', $pricing['designation'], $rule['percent']), // TODO translation / number_format
                        $rule['percent']
                    );
                }
            }
        }

        // TODO Promotions (search using date, return if better)

        return null;
    }

    /**
     * Finds the pricing matching the given product, group and country.
     *
     * @param ProductInterface       $product
     * @param CustomerGroupInterface $group
     * @param CountryInterface       $country
     *
     * @return array
     */
    public function findPricing(ProductInterface $product, ContextInterface $context)
    {
        $hash = implode('-', [
            $context->getCustomerGroup()->getId(),
            $context->getInvoiceCountry()->getId(),
            $product->getBrand()->getId(),
        ]);

        return isset($this->grid[$hash]) ? $this->grid[$hash] : [];
    }
}

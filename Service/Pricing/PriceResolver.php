<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\PricingRepositoryInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentData;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;

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
     * @var CustomerGroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * @var CountryRepositoryInterface
     */
    protected $countryRepository;

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
     * @var CustomerGroupInterface
     */
    protected $defaultCustomerGroup;

    /**
     * @var CountryInterface
     */
    protected $defaultCountry;


    /**
     * Constructor.
     *
     * @param PricingRepositoryInterface       $pricingRepository
     * @param CustomerGroupRepositoryInterface $customerGroupRepository
     * @param CountryRepositoryInterface       $countryRepository
     */
    public function __construct(
        PricingRepositoryInterface $pricingRepository,
        CustomerGroupRepositoryInterface $customerGroupRepository,
        CountryRepositoryInterface $countryRepository
    ) {
        $this->pricingRepository = $pricingRepository;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->countryRepository = $countryRepository;

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
     * @param ProductInterface            $product
     * @param int                         $quantity
     * @param CustomerGroupInterface|null $group
     * @param CountryInterface|null       $country
     *
     * @return \Ekyna\Component\Commerce\Common\Model\AdjustmentData|null
     */
    public function resolve(
        ProductInterface $product,
        $quantity,
        CustomerGroupInterface $group = null,
        CountryInterface $country = null
    ) {
        $pricing = $this->findPricing($product, $group, $country);

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
    public function findPricing(
        ProductInterface $product,
        CustomerGroupInterface $group = null,
        CountryInterface $country = null
    ) {
        if (null === $group) {
            $group = $this->getDefaultCustomerGroup();
        }

        if (null === $country) {
            $country = $this->getDefaultCountry();
        }

        $hash = implode('-', [
            $group->getId(),
            $country->getId(),
            $product->getBrand()->getId(),
        ]);

        return isset($this->grid[$hash]) ? $this->grid[$hash] : [];
    }

    /**
     * Returns the default customer group.
     *
     * @return CustomerGroupInterface
     */
    protected function getDefaultCustomerGroup()
    {
        if (null !== $this->defaultCustomerGroup) {
            return $this->defaultCustomerGroup;
        }

        return $this->defaultCustomerGroup = $this->customerGroupRepository->findDefault();
    }

    /**
     * Returns the default country.
     *
     * @return CountryInterface
     */
    protected function getDefaultCountry()
    {
        if (null !== $this->defaultCountry) {
            return $this->defaultCountry;
        }

        return $this->defaultCountry = $this->countryRepository->findDefault();
    }
}

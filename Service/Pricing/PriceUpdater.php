<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Doctrine\ORM\EntityManager;
use Ekyna\Bundle\ProductBundle\Entity\Price;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes as Types;
use Ekyna\Bundle\ProductBundle\Repository\PriceRepositoryInterface;

/**
 * Class PriceUpdater
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceUpdater
{
    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @var OfferResolver
     */
    protected $offerResolver;

    /**
     * @var PriceRepositoryInterface
     */
    protected $priceRepository;

    /**
     * @var PriceInvalidator
     */
    protected $priceInvalidator;

    /**
     * @var string
     */
    protected $customerGroupClass;

    /**
     * @var string
     */
    protected $countryClass;


    /**
     * Constructor.
     *
     * @param EntityManager            $manager
     * @param OfferResolver            $offerResolver
     * @param PriceRepositoryInterface $priceRepository
     * @param PriceInvalidator         $priceInvalidator
     * @param string                   $customerGroupClass
     * @param string                   $countryClass
     */
    public function __construct(
        EntityManager $manager,
        OfferResolver $offerResolver,
        PriceRepositoryInterface $priceRepository,
        PriceInvalidator $priceInvalidator,
        string $customerGroupClass,
        string $countryClass
    ) {
        $this->manager = $manager;
        $this->offerResolver = $offerResolver;
        $this->priceRepository = $priceRepository;
        $this->priceInvalidator = $priceInvalidator;
        $this->customerGroupClass = $customerGroupClass;
        $this->countryClass = $countryClass;
    }

    /**
     * Updates the product offers.
     *
     * @param Model\ProductInterface $product
     *
     * @return bool Whether this product offers has been updated
     */
    public function updateByProduct(Model\ProductInterface $product): bool
    {
        switch ($product->getType()) {
            case Types::TYPE_SIMPLE:
            case Types::TYPE_VARIANT:
                return $this->updateChildProduct($product);

            case Types::TYPE_VARIABLE:
                return $this->updateVariableProduct($product);

            case Types::TYPE_BUNDLE:
                return $this->updateBundleProduct($product);

            case Types::TYPE_CONFIGURABLE:
                return $this->updateConfigurableProduct($product);

            default:
                throw new InvalidArgumentException("Unexpected product type.");
        }
    }

    /**
     * Updates child product.
     *
     * @param Model\ProductInterface $product
     *
     * @return bool
     */
    protected function updateChildProduct(Model\ProductInterface $product): bool
    {
        $builder = new Config\Builder($this->offerResolver);

        $config = $builder->build($product);

        $keys = $config->getKeys();

        $newPrices = [];

        foreach ($keys as $key) {
            if (null === $price = $builder->flatten($config, $key)->toArray()) {
                continue;
            }

            $newPrices[$key] = $price;
        }

        return $this->update($product, $newPrices);
    }

    /**
     * Update the given variable product's offers.
     *
     * @param Model\ProductInterface $product
     *
     * @return bool
     */
    protected function updateVariableProduct(Model\ProductInterface $product): bool
    {
        $newPrices = [];

        // Gather best offers for each group/country couples
        foreach ($product->getVariants() as $variant) {
            $prices = $this->resolvePrices($variant);

            foreach ($prices as $key => $price) {
                if (isset($newPrices[$key])) {
                    // Replace if lower
                    if ($price['sell_price'] < $newPrices[$key]['sell_price']) {
                        $newPrices[$key] = $price;
                    }
                } else {
                    $newPrices[$key] = $price;
                }
            }
        }

        foreach ($newPrices as &$price) {
            $price['starting_from'] = true;
        }

        return $this->update($product, $newPrices);
    }

    /**
     * Update the given bundle product's offers.
     *
     * @param Model\ProductInterface $product
     *
     * @return bool
     */
    protected function updateBundleProduct(Model\ProductInterface $product): bool
    {
        $builder = new Config\Builder($this->offerResolver);

        $config = $builder->build($product);

        $keys = $config->getKeys();

        $newPrices = [];

        foreach ($keys as $key) {
            if (null === $price = $builder->flatten($config, $key)->toArray()) {
                continue;
            }

            $newPrices[$key] = $price;
        }

        return $this->update($product, $newPrices);
    }

    /**
     * Update the given configurable product's offers.
     *
     * @param Model\ProductInterface $product
     *
     * @return bool
     */
    protected function updateConfigurableProduct(Model\ProductInterface $product): bool
    {
        $newOffers = [];

        return $this->update($product, $newOffers);
    }

    /**
     * Updates the given product prices.
     *
     * @param Model\ProductInterface $product
     * @param array                  $newPrices
     *
     * @return bool
     */
    protected function update(Model\ProductInterface $product, array $newPrices): bool
    {
        // Old prices
        $oldPrices = $this->priceRepository->findByProduct($product, true);

        // Abort if no diff
        if (!$this->hasDiff($oldPrices, $newPrices)) {
            $product->setPendingPrices(false);
            $this->manager->persist($product);

            return false;
        }

        // Delete old prices.
        $qb = $this->manager->createQueryBuilder();
        $qb
            ->delete(Price::class, 'p')
            ->where($qb->expr()->eq('p.product', ':product'))
            ->getQuery()
            ->execute(['product' => $product]);

        // Creates prices
        foreach ($newPrices as $data) {
            $price = new Price();
            $price
                ->setProduct($product)
                ->setStartingFrom($data['starting_from'])
                ->setOriginalPrice($data['original_price'])
                ->setSellPrice($data['sell_price'])
                ->setPercent($data['percent'])
                ->setDetails($data['details']);

            if (!is_null($data['group_id'])) {
                /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface $group */
                $group = $this->manager->getReference($this->customerGroupClass, $data['group_id']);
                $price->setGroup($group);
            }

            if (!is_null($data['country_id'])) {
                /** @var \Ekyna\Component\Commerce\Common\Model\CountryInterface $country */
                $country = $this->manager->getReference($this->countryClass, $data['country_id']);
                $price->setCountry($country);
            }

            $this->manager->persist($price);
        }

        $product->setPendingPrices(false);

        $this->priceInvalidator->invalidateParentsPrices($product);

        $this->manager->persist($product);

        return true;
    }

    /**
     * Resolves the product offers.
     *
     * @param Model\ProductInterface $product
     *
     * @return array
     */
    protected function resolvePrices(Model\ProductInterface $product): array
    {
        $prices = [];

        $data = $this->priceRepository->findByProduct($product, true);
        /** @var array $datum */
        foreach ($data as $datum) {
            $prices[$this->getPriceKey($datum)] = $datum;
        }

        return $prices;
    }

    /**
     * Returns whether new and old prices are different.
     *
     * @param array $oldPrices
     * @param array $newPrices
     *
     * @return bool
     */
    protected function hasDiff(array $oldPrices, array $newPrices): bool
    {
        if (count($oldPrices) != count($newPrices)) {
            return true;
        }

        $fields = [
            'starting_from',
            'original_price',
            'sell_price',
            'percent',
            'details',
            'group_id',
            'country_id',
        ];

        foreach ($newPrices as $new) {
            foreach ($oldPrices as $old) {
                foreach ($fields as $field) {
                    if ($new[$field] != $old[$field]) {
                        continue 2; // Difference, next old
                    }
                }
                continue 2; // Equivalent found, next price
            }

            // Equivalent not found
            return true;
        }

        return false;
    }

    /**
     * Returns the price key (<group_id>-<country_id>).
     *
     * @param array $price
     *
     * @return string
     */
    protected function getPriceKey(array $price): string
    {
        return sprintf('%d-%d', $price['group_id'], $price['country_id']);
    }
}

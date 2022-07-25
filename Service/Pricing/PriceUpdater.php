<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Entity\Price;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes as Types;
use Ekyna\Bundle\ProductBundle\Repository\PriceRepositoryInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;

/**
 * Class PriceUpdater
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceUpdater
{
    protected EntityManagerInterface   $manager;
    protected OfferResolver            $offerResolver;
    protected ResourceFactoryInterface $priceFactory;
    protected PriceRepositoryInterface $priceRepository;
    protected PriceInvalidator         $priceInvalidator;
    protected string                   $customerGroupClass;
    protected string                   $countryClass;

    public function __construct(
        EntityManagerInterface   $manager,
        OfferResolver            $offerResolver,
        ResourceFactoryInterface $priceFactory,
        PriceRepositoryInterface $priceRepository,
        PriceInvalidator         $priceInvalidator,
        string                   $customerGroupClass,
        string                   $countryClass
    ) {
        $this->manager = $manager;
        $this->offerResolver = $offerResolver;
        $this->priceFactory = $priceFactory;
        $this->priceRepository = $priceRepository;
        $this->priceInvalidator = $priceInvalidator;
        $this->customerGroupClass = $customerGroupClass;
        $this->countryClass = $countryClass;
    }

    /**
     * Updates the product prices.
     *
     * @return bool Whether this product prices has been updated
     */
    public function updateProduct(Model\ProductInterface $product): bool
    {
        switch ($product->getType()) {
            case Types::TYPE_SIMPLE:
            case Types::TYPE_VARIANT:
            case Types::TYPE_BUNDLE:
                return $this->updateDefault($product);

            case Types::TYPE_VARIABLE:
                return $this->updateVariable($product);

            case Types::TYPE_CONFIGURABLE:
                return $this->updateConfigurable($product);

            default:
                throw new InvalidArgumentException('Unexpected product type.');
        }
    }

    protected function updateDefault(Model\ProductInterface $product): bool
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
     * Update the given variable product's prices.
     */
    protected function updateVariable(Model\ProductInterface $product): bool
    {
        $newPrices = [];

        // Gather best prices for each group/country couples
        $variants = $product->getVariants();
        foreach ($variants as $variant) {
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
            if (!$price['starting_from'] && 1 < $variants->count()) {
                $price['starting_from'] = true;
            }
        }

        return $this->update($product, $newPrices);
    }

    /**
     * Update the given configurable product's prices.
     */
    protected function updateConfigurable(Model\ProductInterface $product): bool
    {
        return $this->update($product, []);
    }

    /**
     * Updates the given product prices.
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
            ->delete($this->priceRepository->getClassName(), 'p')
            ->where($qb->expr()->eq('p.product', ':product'))
            ->getQuery()
            ->execute(['product' => $product]);

        // Create prices
        foreach ($newPrices as $data) {
            /** @var Price $price */
            $price = $this->priceFactory->create();
            $price
                ->setProduct($product)
                ->setStartingFrom($data['starting_from'])
                ->setOriginalPrice($data['original_price'])
                ->setSellPrice($data['sell_price'])
                ->setPercent($data['percent'])
                ->setDetails($data['details'])
                ->setEndsAt($data['ends_at'] ? new DateTime($data['ends_at']) : null);

            if (!is_null($data['group_id'])) {
                /** @var CustomerGroupInterface $group */
                $group = $this->manager->getReference($this->customerGroupClass, $data['group_id']);
                $price->setGroup($group);
            }

            if (!is_null($data['country_id'])) {
                /** @var CountryInterface $country */
                $country = $this->manager->getReference($this->countryClass, $data['country_id']);
                $price->setCountry($country);
            }

            $this->manager->persist($price);
        }

        $product->setPendingPrices(false);

        $this->priceInvalidator->invalidateParents($product);

        $this->manager->persist($product);

        return true;
    }

    /**
     * Resolves the product prices.
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
            'ends_at',
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
     */
    protected function getPriceKey(array $price): string
    {
        return sprintf('%d-%d', $price['group_id'], $price['country_id']);
    }
}

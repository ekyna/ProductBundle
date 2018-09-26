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
     * @param EntityManager              $manager
     * @param OfferResolver              $offerResolver
     * @param PriceRepositoryInterface   $priceRepository
     * @param PriceInvalidator           $priceInvalidator
     * @param string                     $customerGroupClass
     * @param string                     $countryClass
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
    public function updateByProduct(Model\ProductInterface $product)
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
    protected function updateChildProduct(Model\ProductInterface $product)
    {
        $builder = new Config\Builder($this->offerResolver);

        $config = $builder->build($product);

        $keys = $config->getKeys();

        $newPrices = [];

        foreach ($keys as $key) {
            $newPrices[$key] = $builder->flatten($config, $key)->toArray();
        }

        return $this->update($product, $newPrices);

//        $keys = array_keys($productOffers = $this->resolveOffers($product));
//
//        $config = [
//            'price'   => $product->getNetPrice(),
//            'offers'  => $productOffers,
//            'options' => [],
//        ];
//
//        /** @var Model\OptionGroupInterface[] $optionsGroups */
//        $optionsGroups = $product->getOptionGroups()->toArray();
//        if ($parent = $product->getParent()) {
//            $optionsGroups = array_merge($optionsGroups, $parent->getOptionGroups()->toArray());
//        }
//
//        // Gather all options choices and their offers
//        $mergeOptionsOffers = false;
//        foreach ($optionsGroups as $group) {
//            // Skip non required
//            if (!$group->isRequired()) {
//                continue;
//            }
//
//            $options = [];
//            foreach ($group->getOptions() as $option) {
//                if (null !== $optionProduct = $option->getProduct()) {
//                    if (!empty($optionsOffers = $this->resolveOffers($optionProduct))) {
//                        // At least one option choice has offer(s) : offers merging is required
//                        $mergeOptionsOffers = true;
//                        // Add group/country couples that does not exists in the main product offers
//                        $keys = array_unique(array_merge($keys, array_keys($optionsOffers)));
//                    }
//
//                    // Price from option or option's product
//                    $netPrice = null !== $option->getNetPrice() ? $option->getNetPrice() : $optionProduct->getNetPrice();
//
//                    $options[] = [
//                        'price'  => $netPrice,
//                        'offers' => $optionsOffers,
//                    ];
//                } else {
//                    $options[] = [
//                        'price'  => $option->getNetPrice(),
//                        'offers' => [],
//                    ];
//                }
//            }
//
//            if (!empty($options)) {
//                $config['options'][] = $options;
//            }
//        }
//
//        $newPrices = [];
//
//        // If no option has offer
//        if (!$mergeOptionsOffers) {
//            // Use main product's offers
//            foreach ($config['offers'] as $offer) {
//                $newPrices[] = [
//                    'starting_from'  => false,
//                    'original_price' => $config['price'],
//                    'sell_price'     => round($config['price'] * (1 - $offer['percent'] / 100), 5),
//                    'percent'        => $offer['percent'],
//                    'details'        => $offer['details'],
//                    'group_id'       => $offer['group_id'],
//                    'country_id'     => $offer['country_id'],
//                ];
//            }
//
//            return $this->update($product, $newPrices);
//        }
//
//        // Merge main product's offers with option's ones
//        // For each group/country couples
//        foreach ($keys as $key) {
//            // Original and sell prices
//            $oPrice = $sPrice = $config['price'];
//            // Discounts for each types
//            $discounts = [
//                Offer::TYPE_SPECIAL => [],
//                Offer::TYPE_PRICING => [],
//            ];
//
//            // Product's own offer
//            if (isset($config['offers'][$key])) {
//                $offer = $config['offers'][$key];
//                $sPrice = round($oPrice * (1 - $config['offers'][$key]['percent'] / 100), 5);
//
//                // Store discount amount for each type
//                $base = $oPrice;
//                foreach ([Offer::TYPE_SPECIAL, Offer::TYPE_PRICING] as $type) {
//                    if (!isset($offer['details'][$type])) {
//                        continue;
//                    }
//
//                    $discount = round($oPrice * $offer['details'][$type] / 100, 5);
//                    $discounts[$type][] = $discount;
//                    $base -= $discount;
//                }
//            }
//
//            // For each option group
//            foreach ($config['options'] as $options) {
//                $bestOChild = $bestOPrice = null;
//                $bestSChild = $bestSPrice = null;
//                $bestDiscounts = [];
//
//                // Find best option
//                foreach ($options as $option) {
//                    // Best original price
//                    if (is_null($bestOChild) || $bestOPrice > $option['price']) {
//                        $bestOChild = $option;
//                        $bestOPrice = $option['price'];
//                    }
//
//                    // If option has no offers for this group/country couple, continue to next option
//                    if (!isset($option['offers'][$key])) {
//                        continue;
//                    }
//
//                    $offer = $option['offers'][$key];
//
//                    // If option has the best sell price
//                    $childSPrice = round($option['price'] * (1 - $offer['percent'] / 100), 5);
//                    if (is_null($bestSChild) || $bestSPrice > $childSPrice) {
//                        $bestSChild = $option;
//                        $bestSPrice = $childSPrice;
//
//                        $bestDiscounts = [];
//
//                        // Store discount amount for each type
//                        $base = $option['price'];
//                        foreach ([Offer::TYPE_SPECIAL, Offer::TYPE_PRICING] as $type) {
//                            if (!isset($offer['details'][$type])) {
//                                continue;
//                            }
//
//                            $discount = round($option['price'] * $offer['details'][$type] / 100, 5);
//                            $bestDiscounts[$type] = $discount;
//                            $base -= $discount;
//                        }
//                    }
//                }
//
//                if (is_null($bestOPrice)) {
//                    $bestOPrice = 0;
//                }
//                if (is_null($bestSPrice)) {
//                    $bestSPrice = $bestOPrice;
//                }
//
//                $oPrice += $bestOPrice;
//                $sPrice += $bestSPrice;
//
//                // Add best option's discount amount for each types
//                foreach ([Offer::TYPE_SPECIAL, Offer::TYPE_PRICING] as $type) {
//                    if (isset($bestDiscounts[$type])) {
//                        $discounts[$type][] = $bestDiscounts[$type];
//                    }
//                }
//            }
//
//            // Build price for this group/country couple
//            list($group, $country) = explode('-', $key);
//            if (0 === $group = intval($group)) {
//                $group = null;
//            }
//            if (0 === $country = intval($country)) {
//                $country = null;
//            }
//
//            $details = [];
//            $base = $oPrice;
//            foreach ([Offer::TYPE_SPECIAL, Offer::TYPE_PRICING] as $type) {
//                if (empty($discounts[$type])) {
//                    continue;
//                }
//
//                $discount = array_sum($discounts[$type]);
//                $details[$type] = round($discount * 100 / $base, 5);
//                $base -= $discount;
//            }
//
//            // Add merged offer
//            $newPrices[] = [
//                'details'        => $details,
//                'starting_from'  => true,
//                'original_price' => $oPrice,
//                'sell_price'     => $sPrice,
//                'percent'        => round(($oPrice - $sPrice) * 100 / $oPrice, 2),
//                'group_id'       => $group,
//                'country_id'     => $country,
//            ];
//        }
//
//        return $this->update($product, $newPrices);
    }

    /**
     * Update the given variable product's offers.
     *
     * @param Model\ProductInterface $product
     *
     * @return bool
     */
    protected function updateVariableProduct(Model\ProductInterface $product)
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
    protected function updateBundleProduct(Model\ProductInterface $product)
    {
        $builder = new Config\Builder($this->offerResolver);

        $config = $builder->build($product);

        $keys = $config->getKeys();

        $newPrices = [];

        foreach ($keys as $key) {
            $newPrices[$key] = $builder->flatten($config, $key)->toArray();
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
    protected function updateConfigurableProduct(Model\ProductInterface $product)
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
    protected function update(Model\ProductInterface $product, array $newPrices)
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
                $price->setGroup(
                    $this->manager->getReference($this->customerGroupClass, $data['group_id'])
                );
            }

            if (!is_null($data['country_id'])) {
                $price->setCountry(
                    $this->manager->getReference($this->countryClass, $data['country_id'])
                );
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
    protected function resolveOffers(Model\ProductInterface $product)
    {
        $offers = [];

        foreach ($this->offerResolver->resolve($product) as &$offer) {
            if (1 != $offer['min_qty']) {
                continue;
            }

            $offers[$this->getPriceKey($offer)] = $offer;
        }

        return $offers;
    }

    /**
     * Resolves the product offers.
     *
     * @param Model\ProductInterface $product
     *
     * @return array
     */
    protected function resolvePrices(Model\ProductInterface $product)
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
    protected function hasDiff(array $oldPrices, array $newPrices)
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
    protected function getPriceKey(array $price)
    {
        return sprintf('%d-%d', $price['group_id'], $price['country_id']);
    }
}

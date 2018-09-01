<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Doctrine\ORM\EntityManager;
use Ekyna\Bundle\ProductBundle\Entity\Offer;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\OfferRepository;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;

/**
 * Class OfferUpdater
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferUpdater
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
     * @var OfferInvalidator
     */
    protected $offerInvalidator;

    /**
     * @var OfferRepository
     */
    protected $offerRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var string
     */
    protected $customerGroupClass;

    /**
     * @var string
     */
    protected $countryClass;

    /**
     * @var string
     */
    protected $pricingClass;

    /**
     * @var string
     */
    protected $specialOfferClass;


    /**
     * Constructor.
     *
     * @param EntityManager              $manager
     * @param OfferResolver              $offerResolver
     * @param OfferInvalidator           $offerInvalidator
     * @param OfferRepository            $offerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param string                     $customerGroupClass
     * @param string                     $countryClass
     * @param string                     $pricingClass
     * @param string                     $specialOfferClass
     */
    public function __construct(
        EntityManager $manager,
        OfferResolver $offerResolver,
        OfferInvalidator $offerInvalidator,
        OfferRepository $offerRepository,
        ProductRepositoryInterface $productRepository,
        string $customerGroupClass,
        string $countryClass,
        string $pricingClass,
        string $specialOfferClass
    ) {
        $this->manager = $manager;
        $this->offerResolver = $offerResolver;
        $this->offerInvalidator = $offerInvalidator;
        $this->offerRepository = $offerRepository;
        $this->productRepository = $productRepository;
        $this->customerGroupClass = $customerGroupClass;
        $this->countryClass = $countryClass;
        $this->pricingClass = $pricingClass;
        $this->specialOfferClass = $specialOfferClass;
    }

    /**
     * Updates the product offers.
     *
     * @param ProductInterface $product
     *
     * @return bool Whether this product offers has been updated
     */
    public function updateByProduct(ProductInterface $product)
    {
        switch ($product->getType()) {
            case ProductTypes::TYPE_SIMPLE:
            case ProductTypes::TYPE_VARIANT:
                return $this->updateChildProduct($product);

            case ProductTypes::TYPE_VARIABLE:
                return $this->updateVariableProduct($product);

            case ProductTypes::TYPE_BUNDLE:
                return $this->updateBundleProduct($product);

            case ProductTypes::TYPE_CONFIGURABLE:
                return $this->updateConfigurableProduct($product);

            default:
                throw new InvalidArgumentException("Unexpected product type.");
        }
    }

    /**
     * Updates child product.
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function updateChildProduct(ProductInterface $product)
    {
        return $this->update($product, $this->offerResolver->resolve($product));
    }

    /**
     * Update the given variable product's offers.
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function updateVariableProduct(ProductInterface $product)
    {
        // Build variants best offers per group/country hash
        $newOffers = [];
        foreach ($product->getVariants() as $variant) {
            $offers = $this->offerResolver->resolve($variant);

            foreach ($offers as $offer) {
                // Only offers for 1 quantity
                if (1 != $offer['min_qty']) {
                    continue;
                }

                $key = $this->getOfferKey($offer);

                if (isset($newOffers[$key])) {
                    // Replace if lower
                    if ($offer['net_price'] < $newOffers[$key]['net_price']) {
                        $newOffers[$key] = $offer;
                    }
                } else {
                    $newOffers[$key] = $offer;
                }
            }
        }

        return $this->update($product, $newOffers);
    }

    /**
     * Update the given bundle product's offers.
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function updateBundleProduct(ProductInterface $product)
    {
        $map = [];
        $prices = [];

        // TODO store all products recursively (choice products as bundles and required options)

        foreach ($product->getBundleSlots() as $index => $slot) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();
            $choiceProduct = $choice->getProduct();

            $prices[$index] = $choiceProduct->getNetPrice();

            $offers = $this->offerResolver->resolve($choiceProduct);

            foreach ($offers as $offer) {
                $key = $this->getOfferKey($offer);

                if (!isset($map[$key])) {
                    $map[$key] = [];
                }

                $map[$key][$index] = $offer;
            }
        }

        $newOffers = [];
        foreach ($map as $key => $data) {

        }

        return $this->update($product, $newOffers);
    }

    /**
     * Update the given configurable product's offers.
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function updateConfigurableProduct(ProductInterface $product)
    {
        $newOffers = [];

        return $this->update($product, $newOffers);
    }

    /**
     * Updates the given product offers.
     *
     * @param ProductInterface $product
     * @param array            $newOffers
     *
     * @return bool
     */
    protected function update(ProductInterface $product, array $newOffers)
    {
        // Old offers
        $oldOffers = $this->offerRepository->findByProduct($product, true);

        // Abort if no diff
        if (!$this->hasDiff($oldOffers, $newOffers)) {
            $product->setPendingOffers(false);
            $this->manager->persist($product);

            return false;
        }

        // Delete old offers.
        $qb = $this->manager->createQueryBuilder();
        $qb
            ->delete(Offer::class, 'o')
            ->where($qb->expr()->eq('o.product', ':product'))
            ->getQuery()
            ->execute(['product' => $product]);

        // Creates offers
        foreach ($newOffers as $data) {
            $offer = new Offer();
            $offer
                ->setProduct($product)
                ->setMinQuantity($data['min_qty'])
                ->setPercent($data['percent'])
                ->setNetPrice($data['net_price']);

            if (!is_null($data['group_id'])) {
                $offer->setGroup(
                    $this->manager->getReference($this->customerGroupClass, $data['group_id'])
                );
            }

            if (!is_null($data['country_id'])) {
                $offer->setCountry(
                    $this->manager->getReference($this->countryClass, $data['country_id'])
                );
            }

            if ($data['type'] === Offer::TYPE_PRICING) {
                $offer->setPricing(
                    $this->manager->getReference($this->pricingClass, $data['id'])
                );
            } elseif ($data['type'] === Offer::TYPE_SPECIAL) {
                $offer->setSpecialOffer(
                    $this->manager->getReference($this->specialOfferClass, $data['id'])
                );
            }

            $this->manager->persist($offer);
        }

        $product->setPendingOffers(false);
        $this->manager->persist($product);

        $this->invalidateParents($product);

        return true;
    }

    /**
     * Invalidates product's parents (bundled or option product).
     *
     * @param ProductInterface $product
     */
    protected function invalidateParents(ProductInterface $product)
    {
        if (ProductTypes::TYPE_CONFIGURABLE === $product->getType()) {
            return;
        }

        if (ProductTypes::TYPE_VARIANT === $product->getType()) {
            $this->offerInvalidator->invalidateByProductId($product->getParent()->getId());
        }

        $bundleParents = $this->productRepository->findParentsByBundled($product);
        foreach ($bundleParents as $b) {
            $this->offerInvalidator->invalidateByProductId($b->getId());
        }

        $optionParents = $this->productRepository->findParentsByOptionProduct($product);
        foreach ($optionParents as $o) {
            $this->offerInvalidator->invalidateByProductId($o->getId());
        }
    }

    /**
     * Returns whether new and old offers are different.
     *
     * @param array $oldOffers
     * @param array $newOffers
     *
     * @return bool
     */
    protected function hasDiff(array $oldOffers, array $newOffers)
    {
        if (count($oldOffers) != count($newOffers)) {
            return true;
        }

        $fields = [
            // TODO 'designation'
            'group_id',
            'country_id',
            'min_qty',
            'percent',
            'net_price',
        ];

        foreach ($newOffers as $new) {
            foreach ($oldOffers as $old) {
                foreach ($fields as $field) {
                    if ($new[$field] != $old[$field]) {
                        continue 2; // Difference, next old
                    }
                }
                continue 2; // Equivalent found, next offer
            }

            // Equivalent not found
            return true;
        }

        return false;
    }

    /**
     * Returns the offer key (<group_id>-<country_id>).
     *
     * @param array $offer
     *
     * @return string
     */
    protected function getOfferKey(array $offer)
    {
        return sprintf('%d-%d', $offer['group_id'], $offer['country_id']);
    }
}

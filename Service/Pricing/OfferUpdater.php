<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Entity\Offer;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes as Types;
use Ekyna\Bundle\ProductBundle\Repository\OfferRepositoryInterface;

/**
 * Class OfferUpdater
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferUpdater
{
    protected EntityManagerInterface   $manager;
    protected OfferResolver            $offerResolver;
    protected OfferRepositoryInterface $offerRepository;
    protected PriceInvalidator         $priceInvalidator;
    protected string                   $customerGroupClass;
    protected string                   $countryClass;
    protected string                   $pricingClass;
    protected string                   $specialOfferClass;

    public function __construct(
        EntityManagerInterface   $manager,
        OfferResolver            $offerResolver,
        OfferRepositoryInterface $offerRepository,
        PriceInvalidator         $priceInvalidator,
        string                   $customerGroupClass,
        string                   $countryClass,
        string                   $pricingClass,
        string                   $specialOfferClass
    ) {
        $this->manager = $manager;
        $this->offerResolver = $offerResolver;
        $this->offerRepository = $offerRepository;
        $this->priceInvalidator = $priceInvalidator;
        $this->customerGroupClass = $customerGroupClass;
        $this->countryClass = $countryClass;
        $this->pricingClass = $pricingClass;
        $this->specialOfferClass = $specialOfferClass;
    }

    /**
     * Updates the product offers.
     *
     * @return bool Whether this product offers has been updated
     */
    public function updateByProduct(ProductInterface $product): bool
    {
        if (in_array($product->getType(), [Types::TYPE_VARIABLE, Types::TYPE_CONFIGURABLE], true)) {
            $newOffers = [];
        } else {
            $newOffers = $this->offerResolver->resolve($product);
        }

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

        // Create offers
        foreach ($newOffers as $data) {
            $offer = new Offer();
            $offer
                ->setProduct($product)
                ->setMinQuantity($data['min_qty'])
                ->setPercent($data['percent'])
                ->setNetPrice($data['net_price'])
                ->setDetails($data['details']);

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

            if (isset($data['pricing_id'])) {
                $offer->setPricing(
                    $this->manager->getReference($this->pricingClass, $data['pricing_id'])
                );
            }

            if (isset($data['special_offer_id'])) {
                $offer->setSpecialOffer(
                    $this->manager->getReference($this->specialOfferClass, $data['special_offer_id'])
                );
            }

            $this->manager->persist($offer);
        }

        $product
            ->setPendingOffers(false)
            ->setPendingPrices(true);

        $this->priceInvalidator->invalidateParentsPrices($product);

        $this->manager->persist($product);

        return true;
    }

    /**
     * Returns whether new and old offers are different.
     */
    protected function hasDiff(array $oldOffers, array $newOffers): bool
    {
        if (count($oldOffers) != count($newOffers)) {
            return true;
        }

        $fields = [
            'group_id',
            'country_id',
            'min_qty',
            'percent',
            'net_price',
            'details',
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
}

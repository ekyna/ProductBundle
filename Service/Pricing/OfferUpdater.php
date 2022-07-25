<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Entity\Offer;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\OfferRepositoryInterface;

/**
 * Class OfferUpdater
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferUpdater
{
    public function __construct(
        protected readonly EntityManagerInterface   $manager,
        protected readonly OfferResolver            $offerResolver,
        protected readonly OfferRepositoryInterface $offerRepository,
        protected readonly OfferInvalidator         $offerInvalidator,
        protected readonly PriceInvalidator         $priceInvalidator,
        protected readonly string                   $customerGroupClass,
        protected readonly string                   $countryClass,
        protected readonly string                   $pricingClass,
        protected readonly string                   $specialOfferClass
    ) {
    }

    /**
     * Updates the product offers.
     *
     * @return bool Whether this product offers has been updated
     */
    public function updateProduct(ProductInterface $product): bool
    {
        $newOffers = $this->offerResolver->resolve($product);

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

        $this->offerInvalidator->invalidateParents($product);
        $this->priceInvalidator->invalidateByProduct($product);

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

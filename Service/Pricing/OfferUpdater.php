<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Doctrine\ORM\EntityManager;
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
    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @var OfferResolver
     */
    protected $offerResolver;

    /**
     * @var OfferRepositoryInterface
     */
    protected $offerRepository;

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
     * @param EntityManager            $manager
     * @param OfferResolver            $offerResolver
     * @param OfferRepositoryInterface $offerRepository
     * @param PriceInvalidator         $priceInvalidator
     * @param string                   $customerGroupClass
     * @param string                   $countryClass
     * @param string                   $pricingClass
     * @param string                   $specialOfferClass
     */
    public function __construct(
        EntityManager $manager,
        OfferResolver $offerResolver,
        OfferRepositoryInterface $offerRepository,
        PriceInvalidator $priceInvalidator,
        string $customerGroupClass,
        string $countryClass,
        string $pricingClass,
        string $specialOfferClass
    ) {
        $this->manager            = $manager;
        $this->offerResolver      = $offerResolver;
        $this->offerRepository    = $offerRepository;
        $this->priceInvalidator   = $priceInvalidator;
        $this->customerGroupClass = $customerGroupClass;
        $this->countryClass       = $countryClass;
        $this->pricingClass       = $pricingClass;
        $this->specialOfferClass  = $specialOfferClass;
    }

    /**
     * Updates the product offers.
     *
     * @param ProductInterface $product
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

        // Creates offers
        foreach ($newOffers as $data) {
            $offer = new Offer();
            $offer
                ->setProduct($product)
                ->setMinQuantity($data['min_qty'])
                ->setPercent($data['percent'])
                ->setNetPrice($data['net_price'])
                ->setDetails($data['details']);

            if (!is_null($data['group_id'])) {
                /** @noinspection PhpParamsInspection */
                $offer->setGroup(
                    $this->manager->getReference($this->customerGroupClass, $data['group_id'])
                );
            }

            if (!is_null($data['country_id'])) {
                /** @noinspection PhpParamsInspection */
                $offer->setCountry(
                    $this->manager->getReference($this->countryClass, $data['country_id'])
                );
            }

            if (isset($data['pricing_id'])) {
                /** @noinspection PhpParamsInspection */
                $offer->setPricing(
                    $this->manager->getReference($this->pricingClass, $data['pricing_id'])
                );
            }

            if (isset($data['special_offer_id'])) {
                /** @noinspection PhpParamsInspection */
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
     *
     * @param array $oldOffers
     * @param array $newOffers
     *
     * @return bool
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

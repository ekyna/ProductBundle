<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Decimal\Decimal;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class PriceCacheClearer
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PriceCacheClearer
{
    private bool  $initialized = false;
    private array $groups;
    private array $countries;
    private Cache $cache;

    public function __construct(
        private readonly CustomerGroupRepositoryInterface $customerGroupRepository,
        private readonly CountryRepositoryInterface       $countryRepository,
        private readonly ?CacheItemPoolInterface          $resultCache
    ) {
    }

    public function clearPriceCache(ProductInterface|int $product): void
    {
        if (null === $this->resultCache) {
            return;
        }

        if ($product instanceof ProductInterface) {
            $product = $product->getId();
        }

        if (!$product) {
            return;
        }

        $this->init();

        foreach ($this->groups as $group) {
            foreach ($this->countries as $country) {
                $this->cache->delete(
                    CacheUtil::buildPriceKeyByIds($product, $group, $country)
                );
            }
        }
    }

    public function clearOfferCache(ProductInterface|int $product): void
    {
        if (null === $this->resultCache) {
            return;
        }

        if ($product instanceof ProductInterface) {
            $product = $product->getId();
        }

        if (!$product) {
            return;
        }

        $this->init();

        foreach ($this->groups as $group) {
            foreach ($this->countries as $country) {
                $this->cache->delete(
                    CacheUtil::buildOfferKeyByIds($product, $group, $country)
                );

                $this->cache->delete(
                    CacheUtil::buildOfferKeyByIds($product, $group, $country, new Decimal(1), false)
                );
            }
        }
    }

    private function init(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;

        $this->cache = DoctrineProvider::wrap($this->resultCache);

        $this->groups = $this->customerGroupRepository->getIdentifiers();
        $this->countries = $this->countryRepository->getIdentifiers(true);
    }
}

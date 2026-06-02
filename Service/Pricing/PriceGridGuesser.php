<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\PriceGridRuleRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

use function array_key_exists;

/**
 * Class PriceGridGuesser
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceGridGuesser
{
    private array $cache;

    public function __construct(
        private readonly PriceGridRuleRepositoryInterface $ruleRepository,
    ) {
        $this->onClear();
    }

    public function onClear(): void
    {
        $this->cache = [];
    }

    public function guess(ProductInterface $product, CustomerGroupInterface $group, Decimal $quantity): ?Decimal
    {
        return $this->get($product, $group, $quantity);
    }

    public function has(ProductInterface $product, CustomerGroupInterface $group, Decimal $quantity): bool
    {
        return null !== $this->get($product, $group, $quantity);
    }

    private function get(ProductInterface $product, CustomerGroupInterface $group, Decimal $quantity): ?Decimal
    {
        $key = CacheUtil::buildGridRuleKey($product, $group, $quantity);

        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $rule = $this->ruleRepository->findByProductAndGroupAndQuantity($product, $group, $quantity);

        return $this->cache[$key] = $rule?->getPrice();
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use Ekyna\Bundle\ProductBundle\Entity\StatCount;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\StatCountRepository;

use function array_column;
use function array_combine;
use function array_map;
use function array_replace_recursive;

/**
 * Class StatHelper
 * @package Ekyna\Bundle\ProductBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatHelper
{
    public function __construct(
        private readonly StatCountRepository $statCountRepository,
    ) {
    }

    /**
     * @return array<string, array{orders: int, productions: int}>
     */
    public function getAnnualStatCount(ProductInterface $product): array
    {
        $transform = fn(string $key, array $data): array => array_map(
            fn($v) => [$key => $v],
            array_combine(
                array_column($data, 'year'),
                array_column($data, 'count')
            )
        );

        $orders = $this->statCountRepository->findAnnualStatsByProduct($product);
        $orders = $transform('orders', $orders);

        $productions = $this->statCountRepository->findAnnualStatsByProduct($product, StatCount::SOURCE_MANUFACTURE);
        $productions = $transform('productions', $productions);

        return array_replace_recursive($orders, $productions);
    }
}

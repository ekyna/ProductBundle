<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\StatCountRepository;

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
     * @return array<string, int>
     */
    public function getAnnualSalesStatCount(ProductInterface $product): array
    {
        return $this->statCountRepository->findAnnualStatsByProduct($product);
    }
}

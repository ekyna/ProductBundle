<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Entity\PriceGridRule;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

use function array_combine;
use function array_map;

/**
 * Class PriceGridHelper
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceGridHelper
{
    public static function getPriceGrids(ProductInterface $product): array
    {
        $grids = [];

        foreach ($product->getPriceGrids() as $grid) {
            $rules = $grid->getRules()->toArray();

            $grids[] = [
                'group' => $grid->getGroup()->getName(),
                'rules' => array_combine(
                    array_map(fn(PriceGridRule $rule): int => $rule->getQuantity(), $rules),
                    array_map(fn(PriceGridRule $rule): string => $rule->getPrice()->toFixed(2), $rules),
                )
            ];
        }

        return $grids;
    }
}

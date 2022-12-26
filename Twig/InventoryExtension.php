<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\ProductBundle\Service\Inventory\InventoryHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class InventoryExtension
 * @package Ekyna\Bundle\ProductBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InventoryExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'has_opened_inventory',
                [InventoryHelper::class, 'hasOpenedInventory']
            ),
        ];
    }
}

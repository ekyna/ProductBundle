<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class InventoryProfiles
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InventoryProfiles extends AbstractConstants
{
    const NONE         = 'none';
    const TREATMENT    = 'treatment';
    const RESUPPLY     = 'resupply';
    const OUT_OF_STOCK = 'out_of_stock';
    const ORDERED      = 'ordered';


    /**
     * @inheritdoc
     */
    public static function getConfig(): array
    {
        $prefix = 'ekyna_product.inventory.profile.';

        return [
            static::NONE         => [$prefix . static::NONE],
            static::TREATMENT    => [$prefix . static::TREATMENT],
            static::RESUPPLY     => [$prefix . static::RESUPPLY],
            static::OUT_OF_STOCK => [$prefix . static::OUT_OF_STOCK],
            static::ORDERED      => [$prefix . static::ORDERED],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getTheme(string $constant): ?string
    {
        return null;
    }
}

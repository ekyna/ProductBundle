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
    const NONE      = 'none';
    const TREATMENT = 'treatment';
    const RESUPPLY  = 'resupply';


    /**
     * @inheritdoc
     */
    public static function getConfig()
    {
        $prefix = 'ekyna_product.inventory.profile.';

        return [
            static::NONE      => [$prefix . static::NONE],
            static::TREATMENT => [$prefix . static::TREATMENT],
            static::RESUPPLY  => [$prefix . static::RESUPPLY],
        ];
    }
}
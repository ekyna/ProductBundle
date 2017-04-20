<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class InventoryProfiles
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InventoryProfiles extends AbstractConstants
{
    public const NONE         = 'none';
    public const TREATMENT    = 'treatment';
    public const RESUPPLY     = 'resupply';
    public const OUT_OF_STOCK = 'out_of_stock';
    public const ORDERED      = 'ordered';

    public static function getConfig(): array
    {
        $prefix = 'inventory.profile.';

        return [
            static::NONE         => [$prefix . static::NONE],
            static::TREATMENT    => [$prefix . static::TREATMENT],
            static::RESUPPLY     => [$prefix . static::RESUPPLY],
            static::OUT_OF_STOCK => [$prefix . static::OUT_OF_STOCK],
            static::ORDERED      => [$prefix . static::ORDERED],
        ];
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaProduct';
    }

    public static function getTheme(string $constant): ?string
    {
        return null;
    }
}

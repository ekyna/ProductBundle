<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class ProductReferenceTypes
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductReferenceTypes extends AbstractConstants
{
    public const TYPE_EAN_8        = 'ean8';
    public const TYPE_EAN_13       = 'ean13';
    public const TYPE_MANUFACTURER = 'manufacturer';

    /**
     * @inheritDoc
     */
    public static function getConfig(): array
    {
        $prefix = 'ekyna_product.product_reference.type.';

        return [
            static::TYPE_EAN_8        => [$prefix . static::TYPE_EAN_8],
            static::TYPE_EAN_13       => [$prefix . static::TYPE_EAN_13],
            static::TYPE_MANUFACTURER => [$prefix . static::TYPE_MANUFACTURER],
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

<?php

declare(strict_types=1);

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

    public static function getConfig(): array
    {
        $prefix = 'product_reference.type.';

        return [
            self::TYPE_EAN_8        => [$prefix . self::TYPE_EAN_8],
            self::TYPE_EAN_13       => [$prefix . self::TYPE_EAN_13],
            self::TYPE_MANUFACTURER => [$prefix . self::TYPE_MANUFACTURER],
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

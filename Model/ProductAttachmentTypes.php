<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class ProductAttachmentTypes
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttachmentTypes extends AbstractConstants
{
    public const TERM_OF_SALE = 'term_of_sale';
    public const INSTRUCTIONS = 'instructions';
    public const DATA_SHEET   = 'data_sheet';
    public const DRAWING_3D   = 'drawing_3d';
    public const DRAWING_2D   = 'drawing_2d';

    public static function getConfig(): array
    {
        $prefix = 'product_attachment.type.';

        return [
            self::TERM_OF_SALE => [$prefix . self::TERM_OF_SALE],
            self::INSTRUCTIONS => [$prefix . self::INSTRUCTIONS],
            self::DATA_SHEET   => [$prefix . self::DATA_SHEET],
            self::DRAWING_3D   => [$prefix . self::DRAWING_3D],
            self::DRAWING_2D   => [$prefix . self::DRAWING_2D],
        ];
    }

    public static function getTheme(string $constant): ?string
    {
        return null;
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaProduct';
    }
}

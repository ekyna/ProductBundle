<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use InvalidArgumentException;

/**
 * Class HighlightModes
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class HighlightModes extends AbstractConstants
{
    public const TYPE_BEST_SELLER   = 'bestSeller';
    public const TYPE_CROSS_SELLING = 'crossSelling';

    public const MODE_ALWAYS = 'always';
    public const MODE_AUTO   = 'auto';
    public const MODE_NEVER  = 'never';

    public static function getConfig(): array
    {
        $prefix = 'highlight.mode';

        return [
            self::MODE_ALWAYS => [$prefix . '.' . self::MODE_ALWAYS, 'success'],
            self::MODE_AUTO   => [$prefix . '.' . self::MODE_AUTO,   'default'],
            self::MODE_NEVER  => [$prefix . '.' . self::MODE_NEVER,  'danger'],
        ];
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaProduct';
    }

    public static function isValidType(string $type, bool $throwException = true): bool
    {
        if (in_array($type, [self::TYPE_BEST_SELLER, self::TYPE_CROSS_SELLING], true)) {
            return true;
        }

        if ($throwException) {
            throw new InvalidArgumentException(sprintf('Unknown highlight type "%s"', $type));
        }

        return false;
    }
}

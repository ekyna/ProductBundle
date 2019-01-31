<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class HighlightModes
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class HighlightModes extends AbstractConstants
{
    const TYPE_BEST_SELLER   = 'bestSeller';
    const TYPE_CROSS_SELLING = 'crossSelling';

    const MODE_AUTO   = 0;
    const MODE_ALWAYS = 1;
    const MODE_NEVER  = 2;


    /**
     * @inheritdoc
     */
    public static function getConfig()
    {
        $prefix = 'ekyna_product.highlight.mode';

        return [
            self::MODE_AUTO   => [$prefix . '.auto',   'default'],
            self::MODE_ALWAYS => [$prefix . '.always', 'success'],
            self::MODE_NEVER  => [$prefix . '.never',  'danger'],
        ];
    }

    /**
     * Returns the theme for the given mode.
     *
     * @param int $mode
     *
     * @return string
     */
    public static function getTheme(int $mode)
    {
        self::isValid($mode, true);

        return self::getConfig()[$mode][1];
    }

    /**
     * Returns whether the given type is valid.
     *
     * @param string $type
     * @param bool   $throwException
     *
     * @return bool
     */
    public static function isValidType(string $type, bool $throwException = true)
    {
        if (in_array($type, [self::TYPE_BEST_SELLER, self::TYPE_CROSS_SELLING], true)) {
            return true;
        }

        if ($throwException) {
            throw new \InvalidArgumentException(sprintf('Unknown highlight type "%s"', $type));
        }

        return false;
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

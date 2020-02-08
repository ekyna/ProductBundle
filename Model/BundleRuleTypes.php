<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class BundleRuleTypes
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleRuleTypes extends AbstractConstants
{
    const HIDE_IF_ALL     = 'hide_if_all';
    const HIDE_IF_ANY     = 'hide_if_any';
    const SHOW_IF_ALL     = 'show_if_all';
    const SHOW_IF_ANY     = 'show_if_any';
    const REQUIRED_IF_ALL = 'required_if_all';
    const REQUIRED_IF_ANY = 'required_if_any';


    /**
     * @inheritDoc
     */
    public static function getConfig(): array
    {
        $prefix = 'ekyna_product.bundle_rule.type.';

        return [
            self::HIDE_IF_ALL     => [$prefix . self::HIDE_IF_ALL],
            self::HIDE_IF_ANY     => [$prefix . self::HIDE_IF_ANY],
            self::SHOW_IF_ALL     => [$prefix . self::SHOW_IF_ALL],
            self::SHOW_IF_ANY     => [$prefix . self::SHOW_IF_ANY],
            self::REQUIRED_IF_ALL => [$prefix . self::REQUIRED_IF_ALL],
            self::REQUIRED_IF_ANY => [$prefix . self::REQUIRED_IF_ANY],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getTheme(string $constant): ?string
    {
        return null;
    }

    /**
     * Returns the "* if all" types.
     *
     * @return array
     */
    public static function getIfAllTypes(): array
    {
        return [self::HIDE_IF_ALL, self::SHOW_IF_ALL, self::REQUIRED_IF_ALL];
    }

    /**
     * Returns the "* if any" types.
     *
     * @return array
     */
    public static function getIfAnyTypes(): array
    {
        return [self::HIDE_IF_ANY, self::SHOW_IF_ANY, self::REQUIRED_IF_ANY];
    }
}

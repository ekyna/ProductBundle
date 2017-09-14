<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class ProductTypes
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTypes extends AbstractConstants
{
    const TYPE_SIMPLE       = 'simple';
    const TYPE_VARIABLE     = 'variable';
    const TYPE_VARIANT      = 'variant';
    const TYPE_BUNDLE       = 'bundle';
    const TYPE_CONFIGURABLE = 'configurable';

    /**
     * Supported conversion types map.
     *
     * @var array
     */
    const CONVERSION_MAP = [
        ProductTypes::TYPE_SIMPLE       => [
            ProductTypes::TYPE_VARIABLE,
            //ProductTypes::TYPE_BUNDLE,
        ],
        ProductTypes::TYPE_VARIABLE     => [],
        ProductTypes::TYPE_VARIANT      => [],
        ProductTypes::TYPE_BUNDLE       => [
            //ProductTypes::TYPE_CONFIGURABLE,
        ],
        ProductTypes::TYPE_CONFIGURABLE => [],
    ];

    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_product.product.type.';

        return [
            static::TYPE_SIMPLE       => [$prefix . static::TYPE_SIMPLE, 'default'],
            static::TYPE_VARIABLE     => [$prefix . static::TYPE_VARIABLE, 'primary'],
            static::TYPE_VARIANT      => [$prefix . static::TYPE_VARIANT, 'success'],
            static::TYPE_BUNDLE       => [$prefix . static::TYPE_BUNDLE, 'warning'],
            static::TYPE_CONFIGURABLE => [$prefix . static::TYPE_CONFIGURABLE, 'danger'],
        ];
    }

    /**
     * Returns all the types.
     *
     * @return array
     */
    static public function getTypes()
    {
        return [
            static::TYPE_SIMPLE,
            static::TYPE_VARIABLE,
            static::TYPE_VARIANT,
            static::TYPE_BUNDLE,
            static::TYPE_CONFIGURABLE,
        ];
    }

    /**
     * Returns the "create button" types.
     *
     * @return array
     */
    static public function getCreateTypes()
    {
        return [
            static::TYPE_SIMPLE,
            static::TYPE_VARIABLE,
            static::TYPE_BUNDLE,
            static::TYPE_CONFIGURABLE,
        ];
    }

    /**
     * Returns the 'child' types.
     *
     * @return array
     */
    static public function getChildTypes()
    {
        return [
            ProductTypes::TYPE_SIMPLE,
            ProductTypes::TYPE_VARIANT,
        ];
    }

    /**
     * Returns whether the type is a 'child' one.
     *
     * @param string $type
     *
     * @return bool
     */
    static public function isChildType($type)
    {
        return in_array($type, static::getChildTypes(), true);
    }

    /**
     * Returns the 'parent' types.
     *
     * @return array
     */
    static public function getParentTypes()
    {
        return [
            ProductTypes::TYPE_VARIABLE,
            ProductTypes::TYPE_BUNDLE,
            ProductTypes::TYPE_CONFIGURABLE,
        ];
    }

    /**
     * Returns whether the type is a 'parent' one.
     *
     * @param string $type
     *
     * @return bool
     */
    static public function isParentType($type)
    {
        return in_array($type, static::getParentTypes(), true);
    }

    /**
     * Returns the 'bundled' types.
     *
     * @return array
     */
    static public function getBundledTypes()
    {
        return [
            ProductTypes::TYPE_BUNDLE,
            ProductTypes::TYPE_CONFIGURABLE,
        ];
    }

    /**
     * Returns whether the type is a 'bundled' one.
     *
     * @param string $type
     *
     * @return bool
     */
    static public function isBundled($type)
    {
        return in_array($type, static::getBundledTypes(), true);
    }

    /**
     * Returns the theme for the given state.
     *
     * @param string $state
     *
     * @return string
     */
    static public function getTheme($state)
    {
        static::isValid($state, true);

        return static::getConfig()[$state][1];
    }

    /**
     * Asserts that the product has the 'simple' type.
     *
     * @param ProductInterface $product
     *
     * @throws InvalidArgumentException
     */
    static public function assertSimple(ProductInterface $product)
    {
        static::assertType($product, static::TYPE_SIMPLE);
    }

    /**
     * Asserts that the product has the 'variable' type.
     *
     * @param ProductInterface $product
     *
     * @throws InvalidArgumentException
     */
    static public function assertVariable(ProductInterface $product)
    {
        static::assertType($product, static::TYPE_VARIABLE);
    }

    /**
     * Asserts that the product has the 'variant' type.
     *
     * @param ProductInterface $product
     *
     * @throws InvalidArgumentException
     */
    static public function assertVariant(ProductInterface $product)
    {
        static::assertType($product, static::TYPE_VARIANT);
    }

    /**
     * Asserts that the product has the 'bundle' type.
     *
     * @param ProductInterface $product
     *
     * @throws InvalidArgumentException
     */
    static public function assertBundle(ProductInterface $product)
    {
        static::assertType($product, static::TYPE_BUNDLE);
    }

    /**
     * Asserts that the product has the 'configurable' type.
     *
     * @param ProductInterface $product
     *
     * @throws InvalidArgumentException
     */
    static public function assertConfigurable(ProductInterface $product)
    {
        static::assertType($product, static::TYPE_CONFIGURABLE);
    }

    /**
     * Asserts that the product is of 'child' type.
     *
     * @param ProductInterface $product
     */
    static public function assertChildType(ProductInterface $product)
    {
        if (!static::isChildType($product->getType())) {
            throw new InvalidArgumentException("Expected product of 'child' type.");
        }
    }

    /**
     * Asserts that the product is of 'parent' type.
     *
     * @param ProductInterface $product
     */
    static public function assertParentType(ProductInterface $product)
    {
        if (!static::isParentType($product->getType())) {
            throw new InvalidArgumentException("Expected product of 'parent' type.");
        }
    }

    /**
     * Asserts that the product is of 'bundled' type.
     *
     * @param ProductInterface $product
     */
    static public function assertBundled(ProductInterface $product)
    {
        if (!static::isBundled($product->getType())) {
            throw new InvalidArgumentException("Expected product of 'bundled' type.");
        }
    }

    /**
     * Returns the available types the given product can be converted to.
     *
     * @param ProductInterface $product
     *
     * @return string[]
     */
    static public function getConversionTypes(ProductInterface $product)
    {
        return static::CONVERSION_MAP[$product->getType()];
    }

    /**
     * Asserts that the product has the given type.
     *
     * @param ProductInterface $product
     * @param string           $type
     *
     * @throws InvalidArgumentException
     */
    static private function assertType(ProductInterface $product, $type)
    {
        if (!($product->getType() === $type)) {
            throw new InvalidArgumentException("Expected product of type '$type'.");
        }
    }
}

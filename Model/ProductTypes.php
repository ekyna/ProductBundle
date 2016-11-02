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
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_product.product.type.';

        return [
            static::TYPE_SIMPLE       => [$prefix . static::TYPE_SIMPLE],
            static::TYPE_VARIABLE     => [$prefix . static::TYPE_VARIABLE],
            static::TYPE_VARIANT      => [$prefix . static::TYPE_VARIANT],
            static::TYPE_BUNDLE       => [$prefix . static::TYPE_BUNDLE],
            static::TYPE_CONFIGURABLE => [$prefix . static::TYPE_CONFIGURABLE],
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
     * Returns the 'child' types.
     *
     * @return array
     */
    static public function getChildTypes()
    {
        return [
            ProductTypes::TYPE_SIMPLE,
            ProductTypes::TYPE_VARIANT
        ];
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
            ProductTypes::TYPE_CONFIGURABLE
        ];
    }

    /**
     * Returns whether the given type is valid or not.
     *
     * @param string $type
     *
     * @return bool
     */
    static public function isValidType($type)
    {
        return in_array($type, static::getTypes(), true);
    }

    /**
     * Asserts that the product has the 'simple' type.
     *
     * @param ProductInterface $product
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
    static public function assetChildType(ProductInterface $product)
    {
        if (!in_array($product->getType(), static::getChildTypes(), true)) {
            throw new InvalidArgumentException("Expected product of 'child' type.");
        }
    }

    /**
     * Asserts that the product is of 'parent' type.
     *
     * @param ProductInterface $product
     */
    static public function assetParentType(ProductInterface $product)
    {
        if (!in_array($product->getType(), static::getParentTypes(), true)) {
            throw new InvalidArgumentException("Expected product of 'parent' type.");
        }
    }

    /**
     * Asserts that the product has the given type.
     *
     * @param ProductInterface $product
     * @throws InvalidArgumentException
     */
    static private function assertType(ProductInterface $product, $type)
    {
        if (!($product->getType() === $type)) {
            throw new InvalidArgumentException("Expected product of type '$type'.");
        }
    }
}

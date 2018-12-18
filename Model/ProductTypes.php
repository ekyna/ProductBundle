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
            self::TYPE_SIMPLE       => [$prefix . self::TYPE_SIMPLE,       'indigo'],
            self::TYPE_VARIANT      => [$prefix . self::TYPE_VARIANT,      'light-green'],
            self::TYPE_VARIABLE     => [$prefix . self::TYPE_VARIABLE,     'teal'],
            self::TYPE_BUNDLE       => [$prefix . self::TYPE_BUNDLE,       'purple'],
            self::TYPE_CONFIGURABLE => [$prefix . self::TYPE_CONFIGURABLE, 'red'],
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
            self::TYPE_SIMPLE,
            self::TYPE_VARIANT,
            self::TYPE_VARIABLE,
            self::TYPE_BUNDLE,
            self::TYPE_CONFIGURABLE,
        ];
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
        self::isValid($state, true);

        return self::getConfig()[$state][1];
    }

    /**
     * Returns the "create button" types.
     *
     * @return array
     */
    static public function getCreateTypes()
    {
        return [
            self::TYPE_SIMPLE,
            self::TYPE_VARIABLE,
            self::TYPE_BUNDLE,
            self::TYPE_CONFIGURABLE,
        ];
    }

    /**
     * Returns whether the type is 'simple'.
     *
     * @param ProductInterface|string $type
     *
     * @return bool
     */
    static public function isSimpleType($type)
    {
        return self::TYPE_SIMPLE === self::typeFromProduct($type);
    }

    /**
     * Returns whether the type is 'variant'.
     *
     * @param ProductInterface|string $type
     *
     * @return bool
     */
    static public function isVariantType($type)
    {
        return self::TYPE_VARIANT === self::typeFromProduct($type);
    }

    /**
     * Returns whether the type is 'variable'.
     *
     * @param ProductInterface|string $type
     *
     * @return bool
     */
    static public function isVariableType($type)
    {
        return self::TYPE_VARIABLE === self::typeFromProduct($type);
    }

    /**
     * Returns whether the type is 'bundle'.
     *
     * @param ProductInterface|string $type
     *
     * @return bool
     */
    static public function isBundleType($type)
    {
        return self::TYPE_BUNDLE === self::typeFromProduct($type);
    }

    /**
     * Returns whether the type is 'configurable'.
     *
     * @param ProductInterface|string $type
     *
     * @return bool
     */
    static public function isConfigurableType($type)
    {
        return self::TYPE_CONFIGURABLE === self::typeFromProduct($type);
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
     * @param ProductInterface|string $type
     *
     * @return bool
     */
    static public function isChildType($type)
    {
        return in_array(self::typeFromProduct($type), self::getChildTypes(), true);
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
     * @param ProductInterface|string $type
     *
     * @return bool
     */
    static public function isParentType($type)
    {
        return in_array(self::typeFromProduct($type), self::getParentTypes(), true);
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
     * @param ProductInterface|string $type
     *
     * @return bool
     */
    static public function isBundledType($type)
    {
        return in_array(self::typeFromProduct($type), self::getBundledTypes(), true);
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
        self::assertType($product, self::TYPE_SIMPLE);
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
        self::assertType($product, self::TYPE_VARIABLE);
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
        self::assertType($product, self::TYPE_VARIANT);
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
        self::assertType($product, self::TYPE_BUNDLE);
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
        self::assertType($product, self::TYPE_CONFIGURABLE);
    }

    /**
     * Asserts that the product is of 'child' type.
     *
     * @param ProductInterface $product
     */
    static public function assertChildType(ProductInterface $product)
    {
        if (!self::isChildType($product->getType())) {
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
        if (!self::isParentType($product->getType())) {
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
        if (!self::isBundledType($product->getType())) {
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
        return self::CONVERSION_MAP[$product->getType()];
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
        if ($product->getType() !== $type) {
            throw new InvalidArgumentException("Expected product of type '$type'.");
        }
    }

    /**
     * Returns the type from product.
     *
     * @param ProductInterface|string $type
     *
     * @return string
     */
    static private function typeFromProduct($type)
    {
        if ($type instanceof ProductInterface) {
            return $type->getType();
        }

        if (is_string($type)) {
            return $type;
        }

        throw new InvalidArgumentException("Expected instance of " . ProductInterface::class . " or string.");
    }
}

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
    public const TYPE_SIMPLE       = 'simple';
    public const TYPE_VARIABLE     = 'variable';
    public const TYPE_VARIANT      = 'variant';
    public const TYPE_BUNDLE       = 'bundle';
    public const TYPE_CONFIGURABLE = 'configurable';


    /**
     * {@inheritdoc}
     */
    public static function getConfig(): array
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
    public static function getTypes(): array
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
     * Returns the "create button" types.
     *
     * @return array
     */
    public static function getCreateTypes(): array
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
    public static function isSimpleType($type): bool
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
    public static function isVariantType($type): bool
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
    public static function isVariableType($type): bool
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
    public static function isBundleType($type): bool
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
    public static function isConfigurableType($type): bool
    {
        return self::TYPE_CONFIGURABLE === self::typeFromProduct($type);
    }

    /**
     * Returns the 'child' types.
     *
     * @return array
     */
    public static function getChildTypes(): array
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
    public static function isChildType($type): bool
    {
        return in_array(self::typeFromProduct($type), self::getChildTypes(), true);
    }

    /**
     * Returns the 'parent' types.
     *
     * @return array
     */
    public static function getParentTypes(): array
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
    public static function isParentType($type): bool
    {
        return in_array(self::typeFromProduct($type), self::getParentTypes(), true);
    }

    /**
     * Returns the 'bundled' types.
     *
     * @return array
     */
    public static function getBundledTypes(): array
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
    public static function isBundledType($type): bool
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
    public static function assertSimple(ProductInterface $product): void
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
    public static function assertVariable(ProductInterface $product): void
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
    public static function assertVariant(ProductInterface $product): void
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
    public static function assertBundle(ProductInterface $product): void
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
    public static function assertConfigurable(ProductInterface $product): void
    {
        self::assertType($product, self::TYPE_CONFIGURABLE);
    }

    /**
     * Asserts that the product is of 'child' type.
     *
     * @param ProductInterface $product
     */
    public static function assertChildType(ProductInterface $product): void
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
    public static function assertParentType(ProductInterface $product): void
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
    public static function assertBundled(ProductInterface $product): void
    {
        if (!self::isBundledType($product->getType())) {
            throw new InvalidArgumentException("Expected product of 'bundled' type.");
        }
    }

    /**
     * Asserts that the product has the given type.
     *
     * @param ProductInterface $product
     * @param string           $type
     *
     * @throws InvalidArgumentException
     */
    private static function assertType(ProductInterface $product, string $type): void
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
    private static function typeFromProduct($type): string
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

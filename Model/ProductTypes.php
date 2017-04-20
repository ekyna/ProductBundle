<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class ProductTypes
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductTypes extends AbstractConstants
{
    public const TYPE_SIMPLE       = 'simple';
    public const TYPE_VARIABLE     = 'variable';
    public const TYPE_VARIANT      = 'variant';
    public const TYPE_BUNDLE       = 'bundle';
    public const TYPE_CONFIGURABLE = 'configurable';

    public static function getConfig(): array
    {
        $prefix = 'product.type.';

        return [
            self::TYPE_SIMPLE       => [$prefix . self::TYPE_SIMPLE,       'indigo'],
            self::TYPE_VARIANT      => [$prefix . self::TYPE_VARIANT,      'light-green'],
            self::TYPE_VARIABLE     => [$prefix . self::TYPE_VARIABLE,     'teal'],
            self::TYPE_BUNDLE       => [$prefix . self::TYPE_BUNDLE,       'purple'],
            self::TYPE_CONFIGURABLE => [$prefix . self::TYPE_CONFIGURABLE, 'red'],
        ];
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaProduct';
    }

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
     * @param ProductInterface|string $type
     *
     * @return bool
     */
    public static function isSimpleType($type): bool
    {
        return self::TYPE_SIMPLE === self::typeFromProduct($type);
    }

    /**
     * @param ProductInterface|string $type
     */
    public static function isVariantType($type): bool
    {
        return self::TYPE_VARIANT === self::typeFromProduct($type);
    }

    /**
     * @param ProductInterface|string $type
     */
    public static function isVariableType($type): bool
    {
        return self::TYPE_VARIABLE === self::typeFromProduct($type);
    }

    /**
     * @param ProductInterface|string $type
     */
    public static function isBundleType($type): bool
    {
        return self::TYPE_BUNDLE === self::typeFromProduct($type);
    }

    /**
     * @param ProductInterface|string $type
     */
    public static function isConfigurableType($type): bool
    {
        return self::TYPE_CONFIGURABLE === self::typeFromProduct($type);
    }

    public static function getChildTypes(): array
    {
        return [
            ProductTypes::TYPE_SIMPLE,
            ProductTypes::TYPE_VARIANT,
        ];
    }

    /**
     * @param ProductInterface|string $type
     */
    public static function isChildType($type): bool
    {
        return in_array(self::typeFromProduct($type), self::getChildTypes(), true);
    }

    public static function getParentTypes(): array
    {
        return [
            ProductTypes::TYPE_VARIABLE,
            ProductTypes::TYPE_BUNDLE,
            ProductTypes::TYPE_CONFIGURABLE,
        ];
    }

    /**
     * @param ProductInterface|string $type
     */
    public static function isParentType($type): bool
    {
        return in_array(self::typeFromProduct($type), self::getParentTypes(), true);
    }

    public static function getBundledTypes(): array
    {
        return [
            ProductTypes::TYPE_BUNDLE,
            ProductTypes::TYPE_CONFIGURABLE,
        ];
    }

    /**
     * @param ProductInterface|string $type
     */
    public static function isBundledType($type): bool
    {
        return in_array(self::typeFromProduct($type), self::getBundledTypes(), true);
    }

    public static function assertSimple(ProductInterface $product): void
    {
        self::assertType($product, self::TYPE_SIMPLE);
    }

    public static function assertVariable(ProductInterface $product): void
    {
        self::assertType($product, self::TYPE_VARIABLE);
    }

    public static function assertVariant(ProductInterface $product): void
    {
        self::assertType($product, self::TYPE_VARIANT);
    }

    public static function assertBundle(ProductInterface $product): void
    {
        self::assertType($product, self::TYPE_BUNDLE);
    }

    public static function assertConfigurable(ProductInterface $product): void
    {
        self::assertType($product, self::TYPE_CONFIGURABLE);
    }

    public static function assertChildType(ProductInterface $product): void
    {
        if (!self::isChildType($product->getType())) {
            throw new InvalidArgumentException("Expected product of 'child' type.");
        }
    }

    public static function assertParentType(ProductInterface $product): void
    {
        if (!self::isParentType($product->getType())) {
            throw new InvalidArgumentException("Expected product of 'parent' type.");
        }
    }

    public static function assertBundled(ProductInterface $product): void
    {
        if (!self::isBundledType($product->getType())) {
            throw new InvalidArgumentException("Expected product of 'bundled' type.");
        }
    }

    private static function assertType(ProductInterface $product, string $type): void
    {
        if ($product->getType() !== $type) {
            throw new InvalidArgumentException("Expected product of type '$type'.");
        }
    }

    /**
     * @param ProductInterface|string $type
     */
    private static function typeFromProduct($type): string
    {
        if ($type instanceof ProductInterface) {
            return $type->getType();
        }

        if (is_string($type)) {
            return $type;
        }

        throw new UnexpectedTypeException($type, ['string', ProductInterface::class]);
    }
}

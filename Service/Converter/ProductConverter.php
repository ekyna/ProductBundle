<?php

namespace Ekyna\Bundle\ProductBundle\Service\Converter;

use Ekyna\Bundle\ProductBundle\Event\ConvertEvent;
use Ekyna\Bundle\ProductBundle\Exception\ConvertException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use RuntimeException;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Class ProductConverter
 * @package Ekyna\Bundle\ProductBundle\Service\Converter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductConverter implements RuntimeExtensionInterface
{
    /**
     * @var ConverterInterface[]
     */
    private $converters = [];


    /**
     * Registers the converter.
     *
     * @param ConverterInterface $converter
     */
    public function registerConverter(ConverterInterface $converter): void
    {
        $class = get_class($converter);

        if (isset($this->converters[$class])) {
            throw new RuntimeException('This converter is already registered.');
        }

        $this->converters[$class] = $converter;
    }

    /**
     * Converts the given product to the given type.
     *
     * @param ProductInterface $source
     * @param string           $targetType
     *
     * @return ConvertEvent
     */
    public function convert(ProductInterface $source, string $targetType): ConvertEvent
    {
        $sourceType = $source->getType();

        if ($sourceType === $targetType) {
            throw new RuntimeException("Unexpected conversion type: source and target are the same.");
        }

        return $this->getConverter($sourceType, $targetType)->convert($source);
    }

    /**
     * Returns whether or not the conversion is supported.
     *
     * @param ProductInterface $product
     * @param string           $type
     *
     * @return bool
     */
    public function can(ProductInterface $product, string $type): bool
    {
        try {
            $this->getConverter($product->getType(), $type);

            return true;
        } catch (ConvertException $e) {
        }

        return false;
    }

    /**
     * Returns the supported conversion types.
     *
     * @param string $sourceType
     *
     * @return array
     */
    public function getTargetTypes(string $sourceType): array
    {
        ProductTypes::isValid($sourceType);

        $targetTypes = [];

        foreach ($this->converters as $converter) {
            if (!$converter->supportsSourceType($sourceType)) {
                continue;
            }

            foreach (ProductTypes::getTypes() as $targetType) {
                if ($converter->supportsTargetType($targetType)) {
                    $targetTypes[] = $targetType;
                    continue 2;
                }
            }
        }

        return $targetTypes;
    }

    /**
     * Returns the converter for the given types.
     *
     * @param string $sourceType
     * @param string $targetType
     *
     * @return ConverterInterface
     */
    private function getConverter(string $sourceType, string $targetType): ConverterInterface
    {
        ProductTypes::isValid($sourceType);
        ProductTypes::isValid($targetType);

        foreach ($this->converters as $converter) {
            if (!$converter->supportsSourceType($sourceType)) {
                continue;
            }

            if (!$converter->supportsTargetType($targetType)) {
                continue;
            }

            return $converter;
        }

        throw new ConvertException("Converting '$sourceType' to '$targetType' is not supported");
    }
}

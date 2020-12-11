<?php

namespace Ekyna\Bundle\ProductBundle\Service\Converter;

use Ekyna\Bundle\ProductBundle\Event\ConvertEvent;
use Ekyna\Bundle\ProductBundle\Exception\ConvertException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Interface ConverterInterface
 * @package Ekyna\Bundle\ProductBundle\Service\Converter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ConverterInterface
{
    /**
     * Performs the conversion.
     *
     * @param ProductInterface $source
     *
     * @return ConvertEvent
     *
     * @throws ConvertException
     */
    public function convert(ProductInterface $source): ConvertEvent;

    /**
     * Returns whether the source product type is supported.
     *
     * @param string $type
     *
     * @return bool
     */
    public function supportsSourceType(string $type): bool;

    /**
     * Returns whether the target product type is supported.
     *
     * @param string $type
     *
     * @return bool
     */
    public function supportsTargetType(string $type): bool;
}

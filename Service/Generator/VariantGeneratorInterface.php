<?php

namespace Ekyna\Bundle\ProductBundle\Service\Generator;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Interface VariantGeneratorInterface
 * @package Ekyna\Bundle\ProductBundle\Service\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface VariantGeneratorInterface
{
    /**
     * Generates the variants for the given variable product.
     *
     * @param ProductInterface $product The variable product
     *
     * @return ProductInterface[] The generated variants
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function generateVariants(ProductInterface $product);
}

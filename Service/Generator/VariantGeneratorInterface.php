<?php

declare(strict_types=1);

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
     * @param ProductInterface $variable The variable product
     *
     * @return array<ProductInterface> The generated variants
     */
    public function generateVariants(ProductInterface $variable): array;
}

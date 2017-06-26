<?php

namespace Ekyna\Bundle\ProductBundle\Service\Generator;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Interface ReferenceGeneratorInterface
 * @package Ekyna\Bundle\ProductBundle\Service\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ReferenceGeneratorInterface
{
    /**
     * Generates the reference for the given product.
     *
     * @param ProductInterface $product
     *
     * @return $this|ReferenceGeneratorInterface
     */
    public function generate(ProductInterface $product);
}

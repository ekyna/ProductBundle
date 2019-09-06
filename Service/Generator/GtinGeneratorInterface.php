<?php

namespace Ekyna\Bundle\ProductBundle\Service\Generator;

use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;

/**
 * Interface GtinGeneratorInterface
 * @package Ekyna\Bundle\ProductBundle\Service\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface GtinGeneratorInterface extends GeneratorInterface
{
    /**
     * Sets the manufacturer code.
     *
     * @param string $code
     */
    public function setManufacturerCode(string $code): void;
}

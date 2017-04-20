<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Factory;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Factory\TranslatableFactoryInterface;

/**
 * Interface ProductFactoryInterface
 * @package Ekyna\Bundle\ProductBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ProductFactoryInterface extends TranslatableFactoryInterface
{
    public function createWithType(string $type): ProductInterface;
}

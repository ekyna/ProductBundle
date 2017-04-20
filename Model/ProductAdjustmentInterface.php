<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;

/**
 * Interface ProductAdjustmentInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductAdjustmentInterface extends AdjustmentInterface
{
    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): ProductAdjustmentInterface;
}

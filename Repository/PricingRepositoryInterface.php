<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface PricingRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PricingRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the pricings grid.
     *
     * @return array
     */
    public function getGrid();
}

<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface SpecialOfferRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SpecialOfferRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds special offers rules by product.
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    public function findRulesByProduct(ProductInterface $product);

    /**
     * Returns special offers starting today or ending yesterday.
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface[]
     */
    public function findStartingTodayOrEndingYesterday();
}

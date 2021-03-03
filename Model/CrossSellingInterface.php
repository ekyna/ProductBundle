<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface CrossSellingInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CrossSellingInterface extends SortableInterface, ResourceInterface
{
    /**
     * Returns the source.
     *
     * @return ProductInterface|null
     */
    public function getSource(): ?ProductInterface;

    /**
     * Sets the source.
     *
     * @param ProductInterface|null $source
     *
     * @return $this|CrossSellingInterface
     */
    public function setSource(ProductInterface $source = null): CrossSellingInterface;

    /**
     * Returns the target.
     *
     * @return ProductInterface|null
     */
    public function getTarget(): ?ProductInterface;

    /**
     * Sets the target.
     *
     * @param ProductInterface|null $target
     *
     * @return $this|CrossSellingInterface
     */
    public function setTarget(ProductInterface $target = null): CrossSellingInterface;
}

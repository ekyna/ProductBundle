<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface BundleSlotRuleInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BundleSlotRuleInterface extends BundleRuleInterface, SortableInterface
{
    /**
     * Returns the choice.
     *
     * @return BundleSlotInterface
     */
    public function getSlot();

    /**
     * Sets the choice.
     *
     * @param BundleSlotInterface $slot
     *
     * @return $this|BundleSlotRuleInterface
     */
    public function setSlot(BundleSlotInterface $slot = null);
}

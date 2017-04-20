<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface BundleSlotRuleInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BundleSlotRuleInterface extends BundleRuleInterface, SortableInterface
{
    public function getSlot(): ?BundleSlotInterface;

    public function setSlot(?BundleSlotInterface $slot): BundleSlotRuleInterface;
}

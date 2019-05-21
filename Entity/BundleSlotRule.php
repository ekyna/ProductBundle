<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class BundleSlotRule
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotRule extends AbstractBundleRule implements Model\BundleSlotRuleInterface
{
    use SortableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\BundleSlotInterface
     */
    protected $slot;


    /**
     * Clones the bundle choice rule.
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->slot = null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * @inheritDoc
     */
    public function setSlot(Model\BundleSlotInterface $slot = null)
    {
        if ($this->slot !== $slot) {
            if ($previous = $this->slot) {
                $this->slot = null;
                $previous->removeRule($this);
            }

            if ($this->slot = $slot) {
                $this->slot->addRule($this);
            }
        }

        return $this;
    }
}

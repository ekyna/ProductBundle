<?php

declare(strict_types=1);

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

    protected ?int                       $id = null;
    protected ?Model\BundleSlotInterface $slot;


    public function __clone()
    {
        $this->id = null;
        $this->slot = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlot(): ?Model\BundleSlotInterface
    {
        return $this->slot;
    }

    public function setSlot(?Model\BundleSlotInterface $slot): Model\BundleSlotRuleInterface
    {
        if ($this->slot === $slot) {
            return $this;
        }

        if ($previous = $this->slot) {
            $this->slot = null;
            $previous->removeRule($this);
        }

        if ($this->slot = $slot) {
            $this->slot->addRule($this);
        }

        return $this;
    }
}

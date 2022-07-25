<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\PricingGroupInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class PricingGroup
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PricingGroup extends AbstractResource implements PricingGroupInterface
{
    private ?string $name = null;

    public function __toString(): string
    {
        return $this->name ?? 'New pricing group';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): PricingGroupInterface
    {
        $this->name = $name;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface PricingGroupInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface PricingGroupInterface extends ResourceInterface
{
    public function getName(): ?string;

    public function setName(?string $name): PricingGroupInterface;
}

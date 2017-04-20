<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface BundleRuleInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BundleRuleInterface extends ResourceInterface
{
    public function getType(): ?string;

    public function setType(?string $type): BundleRuleInterface;

    public function getConditions(): ?array;

    public function setConditions(?array $conditions): BundleRuleInterface;
}

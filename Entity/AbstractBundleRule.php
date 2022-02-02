<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\BundleRuleInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class AbstractBundleRule
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractBundleRule extends AbstractResource implements BundleRuleInterface
{
    protected ?string $type       = null;
    protected ?array  $conditions = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): BundleRuleInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getConditions(): ?array
    {
        return $this->conditions;
    }

    public function setConditions(?array $conditions): BundleRuleInterface
    {
        $this->conditions = $conditions;

        return $this;
    }
}

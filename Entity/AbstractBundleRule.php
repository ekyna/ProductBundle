<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\BundleRuleInterface;

/**
 * Class AbstractBundleRule
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractBundleRule implements BundleRuleInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $conditions;


    /**
     * @inheritDoc
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): BundleRuleInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getConditions(): ?array
    {
        return $this->conditions;
    }

    /**
     * @inheritDoc
     */
    public function setConditions(array $conditions): BundleRuleInterface
    {
        $this->conditions = $conditions;

        return $this;
    }
}

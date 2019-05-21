<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface BundleRuleInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BundleRuleInterface extends ResourceInterface
{
    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType(): ?string;

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this|BundleRuleInterface
     */
    public function setType(string $type): BundleRuleInterface;

    /**
     * Returns the conditions.
     *
     * @return array
     */
    public function getConditions(): ?array;

    /**
     * Sets the conditions.
     *
     * @param array $conditions
     *
     * @return $this|BundleRuleInterface
     */
    public function setConditions(array $conditions): BundleRuleInterface;
}

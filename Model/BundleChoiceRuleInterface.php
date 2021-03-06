<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface BundleChoiceRuleInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BundleChoiceRuleInterface extends BundleRuleInterface, SortableInterface
{
    /**
     * Returns the choice.
     *
     * @return BundleChoiceInterface
     */
    public function getChoice();

    /**
     * Sets the choice.
     *
     * @param BundleChoiceInterface $choice
     *
     * @return $this|BundleChoiceRuleInterface
     */
    public function setChoice(BundleChoiceInterface $choice = null);
}

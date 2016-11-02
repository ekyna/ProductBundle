<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface BundleChoiceRuleInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BundleChoiceRuleInterface extends ResourceInterface
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

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType();

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this|BundleChoiceRuleInterface
     */
    public function setType($type);

    /**
     * Returns the rule.
     *
     * @return string
     */
    public function getExpression();

    /**
     * Sets the rule.
     *
     * @param string $expression
     *
     * @return $this|BundleChoiceRuleInterface
     */
    public function setExpression($expression);

    /**
     * Returns the position.
     *
     * @return int
     */
    public function getPosition();

    /**
     * Sets the position.
     *
     * @param int $position
     *
     * @return $this|BundleChoiceRuleInterface
     */
    public function setPosition($position);
}

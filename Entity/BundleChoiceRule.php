<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class BundleChoiceRule
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceRule implements Model\BundleChoiceRuleInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Model\BundleChoiceInterface
     */
    protected $choice;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var integer
     */
    protected $position;


    /**
     * Clones the bundle choice rule.
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->choice = null;
        }
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * @inheritdoc
     */
    public function setChoice(Model\BundleChoiceInterface $choice = null)
    {
        $this->choice = $choice;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @inheritdoc
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}

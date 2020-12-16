<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class BundleChoiceRule
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceRule extends AbstractBundleRule implements Model\BundleChoiceRuleInterface
{
    use SortableTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Model\BundleChoiceInterface
     */
    protected $choice;


    /**
     * Clones the bundle choice rule.
     */
    public function __clone()
    {
        $this->id = null;
        $this->choice = null;
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * @inheritDoc
     */
    public function setChoice(Model\BundleChoiceInterface $choice = null)
    {
        if ($this->choice !== $choice) {
            if ($previous = $this->choice) {
                $this->choice = null;
                $previous->removeRule($this);
            }

            if ($this->choice = $choice) {
                $this->choice->addRule($this);
            }
        }

        return $this;
    }
}

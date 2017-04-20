<?php

declare(strict_types=1);

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

    protected ?int                         $id     = null;
    protected ?Model\BundleChoiceInterface $choice = null;

    public function __clone()
    {
        $this->id = null;
        $this->choice = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChoice(): ?Model\BundleChoiceInterface
    {
        return $this->choice;
    }

    public function setChoice(?Model\BundleChoiceInterface $choice): Model\BundleChoiceRuleInterface
    {
        if ($this->choice === $choice) {
            return $this;
        }

        if ($previous = $this->choice) {
            $this->choice = null;
            $previous->removeRule($this);
        }

        if ($this->choice = $choice) {
            $this->choice->addRule($this);
        }

        return $this;
    }
}

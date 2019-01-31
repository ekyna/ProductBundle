<?php

namespace Ekyna\Bundle\ProductBundle\Model;

/**
 * Trait VisibilityTrait
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait VisibilityTrait
{
    /**
     * @var bool
     */
    protected $visible = false;

    /**
     * @var int
     */
    protected $visibility = 1;


    /**
     * Returns whether this element is visible.
     *
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * Sets whether this element is visible.
     *
     * @param bool $visible
     *
     * @return $this|VisibilityInterface
     */
    public function setVisible(bool $visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Returns the visibility.
     *
     * @return int
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Sets the visibility.
     *
     * @param int $value
     *
     * @return $this|VisibilityInterface
     */
    public function setVisibility(int $value)
    {
        $this->visibility = $value;

        return $this;
    }
}

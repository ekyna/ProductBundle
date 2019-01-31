<?php

namespace Ekyna\Bundle\ProductBundle\Model;

/**
 * Interface VisibilityInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface VisibilityInterface
{
    /**
     * Returns whether this element is visible.
     *
     * @return bool
     */
    public function isVisible();

    /**
     * Sets whether this element is visible.
     *
     * @param bool $visible
     *
     * @return $this|VisibilityInterface
     */
    public function setVisible(bool $visible);

    /**
     * Returns the visibility.
     *
     * @return int
     */
    public function getVisibility();

    /**
     * Sets the visibility.
     *
     * @param int $value
     *
     * @return $this|VisibilityInterface
     */
    public function setVisibility(int $value);
}

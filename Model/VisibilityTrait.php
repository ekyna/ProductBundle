<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

/**
 * Trait VisibilityTrait
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait VisibilityTrait
{
    protected bool $visible = false;
    protected int $visibility = 1;

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): VisibilityInterface
    {
        $this->visible = $visible;

        return $this;
    }

    public function getVisibility(): int
    {
        return $this->visibility;
    }

    public function setVisibility(int $value): VisibilityInterface
    {
        $this->visibility = $value;

        return $this;
    }
}

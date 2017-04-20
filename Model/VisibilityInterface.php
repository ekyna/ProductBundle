<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

/**
 * Interface VisibilityInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface VisibilityInterface
{
    public function isVisible(): bool;

    /**
     * @return $this|VisibilityInterface
     */
    public function setVisible(bool $visible): VisibilityInterface;

    public function getVisibility(): int;

    /**
     * @return $this|VisibilityInterface
     */
    public function setVisibility(int $value): VisibilityInterface;
}

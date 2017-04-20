<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Class ProductBookmark
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductBookmark
{
    private ?int              $id      = null;
    private ?UserInterface    $user    = null;
    private ?ProductInterface $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): ProductBookmark
    {
        $this->user = $user;

        return $this;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): ProductBookmark
    {
        $this->product = $product;

        return $this;
    }
}

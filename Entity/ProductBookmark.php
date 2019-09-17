<?php

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
    /**
     * @var int
     */
    private $id;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var ProductInterface
     */
    private $product;


    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the user.
     *
     * @return UserInterface
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * Sets the user.
     *
     * @param UserInterface $user
     *
     * @return ProductBookmark
     */
    public function setUser(UserInterface $user): ProductBookmark
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     *
     * @return ProductBookmark
     */
    public function setProduct(ProductInterface $product): ProductBookmark
    {
        $this->product = $product;

        return $this;
    }
}

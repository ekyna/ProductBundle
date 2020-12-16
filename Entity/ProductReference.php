<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceInterface;

/**
 * Class ProductReference
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductReference implements ProductReferenceInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $code;


    /**
     * Clones the product reference.
     */
    public function __clone()
    {
        $this->id = null;
        $this->product = null;
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setProduct(ProductInterface $product = null): ProductReferenceInterface
    {
        if ($this->product !== $product) {
            if ($previous = $this->product) {
                $this->product = null;
                $previous->removeReference($this);
            }

            if ($this->product = $product) {
                $this->product->addReference($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType(string $type): ProductReferenceInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function setCode(string $code): ProductReferenceInterface
    {
        $this->code = (string)$code;

        return $this;
    }
}

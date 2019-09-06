<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class ProductReference
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductReferenceInterface extends ResourceInterface
{
    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct(): ?ProductInterface;

    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     *
     * @return $this|ProductReferenceInterface
     */
    public function setProduct(ProductInterface $product = null): ProductReferenceInterface;

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType(): ?string;

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this|ProductReferenceInterface
     */
    public function setType(string $type): ProductReferenceInterface;

    /**
     * Returns the code.
     *
     * @return string
     */
    public function getCode(): ?string;

    /**
     * Sets the code.
     *
     * @param string $code
     *
     * @return $this|ProductReferenceInterface
     */
    public function setCode(string $code): ProductReferenceInterface;
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class ProductReference
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductReferenceInterface extends ResourceInterface
{
    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): ProductReferenceInterface;

    public function getType(): ?string;

    public function setType(string $type): ProductReferenceInterface;

    public function getCode(): ?string;

    public function setCode(string $code): ProductReferenceInterface;
}

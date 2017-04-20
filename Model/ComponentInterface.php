<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Decimal\Decimal;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class Component
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ComponentInterface extends ResourceInterface
{
    public function getParent(): ?ProductInterface;

    public function setParent(?ProductInterface $parent): ComponentInterface;

    public function getChild(): ?ProductInterface;

    public function setChild(?ProductInterface $child): ComponentInterface;

    public function getQuantity(): Decimal;

    public function setQuantity(Decimal $quantity): ComponentInterface;

    public function getNetPrice(): ?Decimal;

    public function setNetPrice(?Decimal $price): ComponentInterface;
}

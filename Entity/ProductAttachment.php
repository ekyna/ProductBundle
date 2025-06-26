<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\ProductAttachmentInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Entity\AbstractAttachment;
use Ekyna\Component\Resource\Model\LocalizedTrait;

/**
 * Class ProductAttachment
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttachment extends AbstractAttachment implements ProductAttachmentInterface
{
    use LocalizedTrait;

    protected ?ProductInterface $product = null;

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): ProductAttachmentInterface
    {
        if ($product === $this->product) {
            return $this;
        }

        if ($previous = $this->product) {
            $this->product = null;
            $previous->removeAttachment($this);
        }

        if ($this->product = $product) {
            $this->product->addAttachment($this);
        }

        return $this;
    }
}

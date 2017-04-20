<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductMentionInterface;
use Ekyna\Component\Commerce\Common\Entity\AbstractMention;

/**
 * Class ProductMention
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductMention extends AbstractMention implements ProductMentionInterface
{
    private ?ProductInterface $product = null;

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): ProductMentionInterface
    {
        if ($this->product === $product) {
            return $this;
        }

        if ($previous = $this->product) {
            $this->product = null;
            $previous->removeMention($this);
        }

        if ($this->product = $product) {
            $this->product->addMention($this);
        }

        return $this;
    }
}

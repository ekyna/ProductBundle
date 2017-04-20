<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\CrossSellingInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class CrossSelling
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CrossSelling implements CrossSellingInterface
{
    use SortableTrait;

    protected ?int              $id     = null;
    protected ?ProductInterface $source = null;
    protected ?ProductInterface $target = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSource(): ?ProductInterface
    {
        return $this->source;
    }

    public function setSource(?ProductInterface $source): CrossSellingInterface
    {
        $this->source = $source;

        return $this;
    }

    public function getTarget(): ?ProductInterface
    {
        return $this->target;
    }

    public function setTarget(?ProductInterface $target): CrossSellingInterface
    {
        $this->target = $target;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\CrossSellingInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class CrossSelling
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CrossSelling extends AbstractResource implements CrossSellingInterface
{
    use SortableTrait;

    protected ?ProductInterface $source = null;
    protected ?ProductInterface $target = null;

    public function __clone()
    {
        parent::__clone();

        $this->source = null;
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

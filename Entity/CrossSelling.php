<?php

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

    /**
     * @var int
     */
    protected $id;

    /**
     * @var ProductInterface
     */
    protected $source;

    /**
     * @var ProductInterface
     */
    protected $target;


    /**
     * @inheritDoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getSource(): ?ProductInterface
    {
        return $this->source;
    }

    /**
     * @inheritDoc
     */
    public function setSource(ProductInterface $source = null): CrossSellingInterface
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTarget(): ?ProductInterface
    {
        return $this->target;
    }

    /**
     * @inheritDoc
     */
    public function setTarget(ProductInterface $target = null): CrossSellingInterface
    {
        $this->target = $target;

        return $this;
    }
}

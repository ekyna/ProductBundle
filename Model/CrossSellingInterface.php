<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface CrossSellingInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CrossSellingInterface extends SortableInterface, ResourceInterface
{
    public function getSource(): ?ProductInterface;

    public function setSource(?ProductInterface $source): CrossSellingInterface;

    public function getTarget(): ?ProductInterface;

    public function setTarget(?ProductInterface $target): CrossSellingInterface;
}

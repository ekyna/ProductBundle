<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\MediaBundle\Model\GalleryMediaInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ProductMediaInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductMediaInterface extends GalleryMediaInterface, ResourceInterface
{
    public function setProduct(?ProductInterface $product): ProductMediaInterface;

    public function getProduct(): ?ProductInterface;
}

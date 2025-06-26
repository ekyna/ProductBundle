<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;
use Ekyna\Component\Resource\Model\LocalizedInterface;

/**
 * Interface ProductAttachmentInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductAttachmentInterface extends AttachmentInterface, LocalizedInterface
{
    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): ProductAttachmentInterface;
}

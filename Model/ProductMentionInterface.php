<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Commerce\Common\Model\MentionInterface;

/**
 * Interface ProductMentionInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ProductMentionInterface extends MentionInterface
{
    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): ProductMentionInterface;
}

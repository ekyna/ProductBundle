<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface BundleChoiceRuleInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BundleChoiceRuleInterface extends BundleRuleInterface, SortableInterface
{
    public function getChoice(): ?BundleChoiceInterface;

    public function setChoice(?BundleChoiceInterface $choice): BundleChoiceRuleInterface;
}

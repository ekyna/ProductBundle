<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;

/**
 * Interface ProductFilterInterface
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductFilterInterface
{
    /**
     * Sets the context.
     *
     * @param ContextInterface $context
     */
    public function setContext(ContextInterface $context);

    /**
     * Returns whether the product is available.
     *
     * @param Model\ProductInterface $product
     * @param array                  $exclude The option groups ids to exclude
     *
     * @return bool
     */
    public function isProductAvailable(Model\ProductInterface $product, array $exclude = []): bool;

    /**
     * Returns the available variants for the given variable product.
     *
     * @param Model\ProductInterface $product The variable product
     *
     * @return Model\ProductInterface[] The available variants
     */
    public function getVariants(Model\ProductInterface $product): array;

    /**
     * Returns the available slots for the given configurable product.
     *
     * @param Model\ProductInterface $product The configurable product
     *
     * @return Model\BundleSlotInterface[] The available bundle slots
     */
    public function getBundleSlots(Model\ProductInterface $product): array;

    /**
     * Returns the available bundle slot choices for the given bundle slot.
     *
     * @param Model\BundleSlotInterface $slot The bundle slot
     *
     * @return Model\BundleChoiceInterface[] The available bundle slot choices
     */
    public function getSlotChoices(Model\BundleSlotInterface $slot): array;

    /**
     * Returns the available option groups for the given product.
     *
     * @param Model\ProductInterface $product The product
     * @param array                  $exclude The option groups ids to exclude
     *
     * @return Model\OptionGroupInterface[] The available option groups
     */
    public function getOptionGroups(Model\ProductInterface $product, array $exclude = []): array;

    /**
     * Returns the available options for the given option group.
     *
     * @param Model\OptionGroupInterface $group The option group
     *
     * @return Model\OptionInterface[] The options
     */
    public function getGroupOptions(Model\OptionGroupInterface $group): array;
}

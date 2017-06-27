<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Interface ProductFilterInterface
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductFilterInterface
{
    /**
     * Sets the customer group.
     *
     * @param CustomerGroupInterface $group
     */
    public function setCustomerGroup(CustomerGroupInterface $group = null);

    /**
     * Returns whether the product is available.
     *
     * @param Model\ProductInterface $product
     *
     * @return bool
     */
    public function isProductAvailable(Model\ProductInterface $product);

    /**
     * Returns the available variants for the given variable product.
     *
     * @param Model\ProductInterface $product The variable product
     *
     * @return Model\ProductInterface[] The available variants
     */
    public function getVariants(Model\ProductInterface $product);

    /**
     * Returns the available slots for the given configurable product.
     *
     * @param Model\ProductInterface $product The configurable product
     *
     * @return Model\BundleSlotInterface[] The available bundle slots
     */
    public function getBundleSlots(Model\ProductInterface $product);

    /**
     * Returns the available bundle slot choices for the given bundle slot.
     *
     * @param Model\BundleSlotInterface $slot The bundle slot
     *
     * @return Model\BundleChoiceInterface[] The available bundle slot choices
     */
    public function getSlotChoices(Model\BundleSlotInterface $slot);

    /**
     * Returns the available option groups for the given product.
     *
     * @param Model\ProductInterface $product The product
     *
     * @return Model\OptionGroupInterface[] The available option groups
     */
    public function getOptionGroups(Model\ProductInterface $product);

    /**
     * Returns the available options for the given option group.
     *
     * @param Model\OptionGroupInterface $group The option group
     *
     * @return Model\OptionInterface[] The options
     */
    public function getGroupOptions(Model\OptionGroupInterface $group);
}

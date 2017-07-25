<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;

/**
 * Class ProductFilter
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductFilter implements ProductFilterInterface
{
    /**
     * @var CustomerProviderInterface
     */
    private $customerProvider;

    /**
     * @var CustomerGroupInterface
     */
    private $customerGroup;

    /**
     * @var array
     */
    private $productCache = [];

    /**
     * @var array
     */
    private $variantCache = [];

    /**
     * @var array
     */
    private $slotCache = [];

    /**
     * @var array
     */
    private $choiceCache = [];

    /**
     * @var array
     */
    private $groupCache = [];

    /**
     * @var array
     */
    private $optionCache = [];


    /**
     * Constructor.
     *
     * @param CustomerProviderInterface $customerProvider
     */
    public function __construct(CustomerProviderInterface $customerProvider)
    {
        $this->customerProvider = $customerProvider;
    }

    /**
     * @inheritdoc
     */
    public function isProductAvailable(Model\ProductInterface $product)
    {
        if (isset($this->productCache[$product->getId()])) {
            return $this->productCache[$product->getId()];
        }

        $available = true;
        if (!empty($customerGroups = $product->getCustomerGroups()->toArray())) {
            $available = in_array($this->getCustomerGroup(), $customerGroups, true);
        }

        if ($available) {
            if (Model\ProductTypes::TYPE_VARIABLE === $product->getType()) {
                if (empty($this->getVariants($product))) {
                    $available = false;
                }
            } elseif (Model\ProductTypes::isBundled($product->getType())) {
                foreach ($product->getBundleSlots() as $bundleSlot) {
                    if ($bundleSlot->isRequired() && empty($this->getSlotChoices($bundleSlot))) {
                        $available = false;
                        break;
                    }
                }
            }
        }

        if ($available) {
            foreach ($product->getOptionGroups() as $optionGroup) {
                if ($optionGroup->isRequired() && empty($this->getGroupOptions($optionGroup))) {
                    $available = false;
                    break;
                }
            }
        }

        return $this->productCache[$product->getId()] = $available;
    }

    /**
     * @inheritdoc
     */
    public function getVariants(Model\ProductInterface $product)
    {
        Model\ProductTypes::assertVariable($product);

        if (isset($this->variantCache[$product->getId()])) {
            return $this->variantCache[$product->getId()];
        }

        $variants = [];
        foreach ($product->getVariants() as $variant) {
            if ($this->isProductAvailable($variant)) {
                $variants[] = $variant;
            }
        }

        return $this->variantCache[$product->getId()] = $variants;
    }

    /**
     * @inheritdoc
     */
    public function getBundleSlots(Model\ProductInterface $product)
    {
        Model\ProductTypes::assertBundled($product);

        if (isset($this->slotCache[$product->getId()])) {
            $this->slotCache[$product->getId()];
        }

        $slots = [];
        foreach ($product->getBundleSlots() as $slot) {
            if (!empty($this->getSlotChoices($slot))) {
                $slots[] = $slot;
            }
        }

        return $this->slotCache[$product->getId()] = $slots;
    }

    /**
     * @inheritdoc
     */
    public function getSlotChoices(Model\BundleSlotInterface $slot)
    {
        if (isset($this->choiceCache[$slot->getId()])) {
            $this->choiceCache[$slot->getId()];
        }

        $choices = [];
        foreach ($slot->getChoices() as $choice) {
            if ($this->isChoiceAvailable($choice)) {
                $choices[] = $choice;
            }
        }

        return $this->choiceCache[$slot->getId()] = $choices;
    }

    /**
     * @inheritdoc
     */
    public function getOptionGroups(Model\ProductInterface $product)
    {
        if (isset($this->groupCache[$product->getId()])) {
            $this->groupCache[$product->getId()];
        }

        $groups = [];
        foreach ($product->getOptionGroups() as $group) {
            if (!empty($this->getGroupOptions($group))) {
                $groups[] = $group;
            }
        }

        return $this->groupCache[$product->getId()] = $groups;
    }

    /**
     * @inheritdoc
     */
    public function getGroupOptions(Model\OptionGroupInterface $group)
    {
        if (isset($this->optionCache[$group->getId()])) {
            $this->optionCache[$group->getId()];
        }

        $options = [];
        foreach ($group->getOptions() as $option) {
            if ($this->isOptionAvailable($option)) {
                $options[] = $option;
            }
        }

        return $this->optionCache[$group->getId()] = $options;
    }

    /**
     * Sets the customer group.
     *
     * @param CustomerGroupInterface $group
     */
    public function setCustomerGroup(CustomerGroupInterface $group = null)
    {
        $this->customerGroup = $group;
    }

    /**
     * Returns the customer group (of logged in customer or current sale).
     *
     * @return CustomerGroupInterface
     */
    protected function getCustomerGroup()
    {
        if (null === $this->customerGroup) {
            $this->customerGroup = $this->customerProvider->getCustomerGroup();
        }

        return $this->customerGroup;
    }

    /**
     * Returns whether the given bundle slot choice is available.
     *
     * @param Model\BundleChoiceInterface $choice
     *
     * @return bool
     */
    protected function isChoiceAvailable(Model\BundleChoiceInterface $choice)
    {
        return $this->isProductAvailable($choice->getProduct());
    }

    /**
     * Returns whether the option is available.
     *
     * @param Model\OptionInterface $option
     *
     * @return bool
     */
    protected function isOptionAvailable(Model\OptionInterface $option)
    {
        if (null === $product = $option->getProduct()) {
            return true;
        }

        return $this->isProductAvailable($product);
    }
}

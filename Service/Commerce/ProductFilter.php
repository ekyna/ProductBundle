<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;

/**
 * Class ProductFilter
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductFilter implements ProductFilterInterface
{
    /**
     * @var array
     */
    private $productCache;

    /**
     * @var array
     */
    private $variantCache;

    /**
     * @var array
     */
    private $slotCache;

    /**
     * @var array
     */
    private $choiceCache;

    /**
     * @var array
     */
    private $groupCache;

    /**
     * @var array
     */
    private $optionCache;

    /**
     * @var ContextInterface
     */
    private $context;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clearCache();
    }

    /**
     * @inheritdoc
     */
    public function setContext(ContextInterface $context)
    {
        $this->context = $context;

        $this->clearCache();
    }

    /**
     * @inheritdoc
     */
    public function isProductAvailable(Model\ProductInterface $product)
    {
        if ($this->hasProductAvailability($product)) {
            return $this->getProductAvailability($product);
        }

        $available = true;

        // Not available if customer group miss match and user is not admin.
        if (!$this->context->isAdmin() && !empty($customerGroups = $product->getCustomerGroups()->toArray())) {
            $available = in_array($this->context->getCustomerGroup(), $customerGroups, true);
        }

        if ($available) {
            if (Model\ProductTypes::TYPE_VARIABLE === $product->getType()) {
                // Not available if a variable has no available variants
                if (empty($this->getVariants($product))) {
                    $available = false;
                }
            } elseif (Model\ProductTypes::isBundled($product->getType())) {
                // Not available if a required bundle slot has no available choices
                foreach ($product->getBundleSlots() as $bundleSlot) {
                    if ($bundleSlot->isRequired() && empty($this->getSlotChoices($bundleSlot))) {
                        $available = false;
                        break;
                    }
                }
            }
        }

        if ($available) {
            // Not available if a required option group has no available choices
            foreach ($product->getOptionGroups() as $optionGroup) {
                if ($optionGroup->isRequired() && empty($this->getGroupOptions($optionGroup))) {
                    $available = false;
                    break;
                }
            }
        }

        return $this->setProductAvailability($product, $available);
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
            // Skip if variant is not visible and user is not admin
            if (!$variant->isVisible() && !$this->context->isAdmin()) {
                continue;
            }
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
            return $this->slotCache[$product->getId()];
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
            return $this->choiceCache[$slot->getId()];
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
            return $this->groupCache[$product->getId()];
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
            return $this->optionCache[$group->getId()];
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

    /**
     * Returns whether the product availability is cached.
     *
     * @param Model\ProductInterface $product
     *
     * @return bool
     */
    protected function hasProductAvailability(Model\ProductInterface $product)
    {
        return isset($this->productCache[$product->getId()]);
    }

    /**
     * Returns the cached product availability.
     *
     * @param Model\ProductInterface $product
     *
     * @return bool
     */
    protected function getProductAvailability(Model\ProductInterface $product)
    {
        return $this->productCache[$product->getId()];
    }

    /**
     * Sets the cached product availability.
     *
     * @param Model\ProductInterface $product   The product
     * @param bool                   $available Whether the product is available
     *
     * @return bool                  The defined availability
     */
    protected function setProductAvailability(Model\ProductInterface $product, $available)
    {
        return $this->productCache[$product->getId()] = (bool)$available;
    }

    /**
     * Clears the results cache.
     */
    protected function clearCache()
    {
        $this->productCache = [];
        $this->variantCache = [];
        $this->slotCache = [];
        $this->choiceCache = [];
        $this->groupCache = [];
        $this->optionCache = [];
    }
}


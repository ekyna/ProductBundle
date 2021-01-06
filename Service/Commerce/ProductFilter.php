<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;

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
    public function isProductAvailable(Model\ProductInterface $product, array $exclude = []): bool
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
            } elseif (Model\ProductTypes::isBundledType($product->getType())) {
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
                // SKip excluded option group
                if (in_array($optionGroup->getId(), $exclude)) {
                    continue;
                }

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
    public function getVariants(Model\ProductInterface $product): array
    {
        Model\ProductTypes::assertVariable($product);

        if (isset($this->variantCache[$product->getId()])) {
            return $this->variantCache[$product->getId()];
        }

        $variants = [];
        foreach ($product->getVariants() as $variant) {
            // Skip if variant is not visible and user is not admin
            // TODO temporary disabled: Sometimes we need invisible variants in bundle slot choices
            /*if (!$variant->isVisible() && !$this->context->isAdmin()) {
                continue;
            }*/
            if ($this->isProductAvailable($variant)) {
                $variants[] = $variant;
            }
        }

        $this->sortChoices($variants);

        return $this->variantCache[$product->getId()] = $variants;
    }

    /**
     * @inheritdoc
     */
    public function getBundleSlots(Model\ProductInterface $product): array
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
    public function getSlotChoices(Model\BundleSlotInterface $slot): array
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

        $this->sortChoices($choices, function (Model\BundleChoiceInterface $choice) {
            return $choice->getProduct();
        });

        return $this->choiceCache[$slot->getId()] = $choices;
    }

    /**
     * @inheritdoc
     */
    public function getOptionGroups(Model\ProductInterface $product, array $exclude = []): array
    {
        $key = implode('-', $exclude);

        if (!isset($this->groupCache[$product->getId()])) {
            $this->groupCache[$product->getId()] = [];
        }

        if (isset($this->groupCache[$product->getId()][$key])) {
            return $this->groupCache[$product->getId()][$key];
        }

        $groups = [];
        foreach ($product->getOptionGroups() as $group) {
            if (in_array($group->getId(), $exclude)) {
                continue;
            }

            if (!empty($this->getGroupOptions($group))) {
                $groups[] = $group;
            }
        }

        return $this->groupCache[$product->getId()][$key] = $groups;
    }

    /**
     * @inheritdoc
     */
    public function getGroupOptions(Model\OptionGroupInterface $group): array
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

        $this->sortChoices($options, function (Model\OptionInterface $option) {
            return $option->getProduct();
        });

        return $this->optionCache[$group->getId()] = $options;
    }

    /**
     * Returns whether the given bundle slot choice is available.
     *
     * @param Model\BundleChoiceInterface $choice
     *
     * @return bool
     */
    protected function isChoiceAvailable(Model\BundleChoiceInterface $choice): bool
    {
        $product = $choice->getProduct();

        if ($this->hasReservedAvailability($product)) {
            return false;
        }

        return $this->isProductAvailable($choice->getProduct(), $choice->getExcludedOptionGroups());
    }

    /**
     * Returns whether the option is available.
     *
     * @param Model\OptionInterface $option
     *
     * @return bool
     */
    protected function isOptionAvailable(Model\OptionInterface $option): bool
    {
        if (null === $product = $option->getProduct()) {
            return true;
        }

        if ($this->hasReservedAvailability($product)) {
            return false;
        }

        return $this->isProductAvailable($product);
    }

    /**
     * Returns whether the product has reserved availability.
     *
     * @param Model\ProductInterface $product
     *
     * @return bool
     */
    protected function hasReservedAvailability(Model\ProductInterface $product): bool
    {
        if ($this->context->isAdmin()) {
            return false;
        }

        if ($product->isQuoteOnly()) {
            return true;
        }

        if ($product->isEndOfLife() && ($product->getStockState() !== StockSubjectStates::STATE_IN_STOCK)) {
            return true;
        }

        return false;
    }

    /**
     * Returns whether the product availability is cached.
     *
     * @param Model\ProductInterface $product
     *
     * @return bool
     */
    protected function hasProductAvailability(Model\ProductInterface $product): bool
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
    protected function getProductAvailability(Model\ProductInterface $product): bool
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
    protected function setProductAvailability(Model\ProductInterface $product, bool $available): bool
    {
        return $this->productCache[$product->getId()] = $available;
    }

    /**
     * Clears the results cache.
     */
    protected function clearCache(): void
    {
        $this->productCache = [];
        $this->slotCache = [];
        $this->choiceCache = [];
        $this->groupCache = [];
        $this->optionCache = [];
    }

    /**
     * Sort the choices by availability.
     *
     * @param array         $choices
     * @param callable|null $getter
     */
    protected function sortChoices(array &$choices, callable $getter = null): void
    {
        usort($choices, function ($a, $b) use ($getter) {
            if ($getter) {
                $a = $getter($a);
                $b = $getter($b);
            }

            if (!$a || !$b) {
                return 0;
            }

            if (!$a instanceof Model\ProductInterface || !$b instanceof Model\ProductInterface) {
                throw new RuntimeException("Expected instance of " . Model\ProductInterface::class);
            }

            if ($a->getAvailableStock() && !$b->getAvailableStock()) {
                return -1;
            }

            if (!$a->getAvailableStock() && $b->getAvailableStock()) {
                return 1;
            }

            return 0;
        });
    }
}


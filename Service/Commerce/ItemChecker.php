<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\CommerceBundle\Exception\InvalidSaleItemException;
use Ekyna\Bundle\CommerceBundle\Service\Checker\ItemCheckerInterface;
use Ekyna\Bundle\ProductBundle\Entity\Component;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface;
use Ekyna\Bundle\ProductBundle\Model\OptionInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

use function array_merge;
use function array_unique;
use function in_array;
use function is_null;

/**
 * Class ItemChecker
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * Options exclusion logic must be kept in sync with ItemChecker,
 * until a new « Add to sale model » is introduced, with composition tree
 * and options bubbling (to get a single level of public children).
 */
class ItemChecker implements ItemCheckerInterface
{
    public function __construct(
        protected readonly ContextProviderInterface $contextProvider,
        protected readonly SubjectHelperInterface   $subjectHelper,
        protected readonly ProductFilterInterface   $filter,
    ) {
    }

    public function initialize(SaleInterface $sale): void
    {
        $context = $this->contextProvider->getContext($sale);

        $this->filter->setContext($context);
    }

    public function check(SaleItemInterface $item): void
    {
        $this->checkItem($item, []);
    }

    /**
     * @throws InvalidSaleItemException
     */
    private function checkItem(SaleItemInterface $item, ?array $exclude): void
    {
        $product = $this->subjectHelper->resolve($item);

        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->configureFilter($item);

        $this->checkBundleSlots($item, $exclude);

        $this->checkComponents($item);

        if (!is_null($exclude)) {
            $this->checkOptions($item, $exclude);
        }
    }

    protected function configureFilter(SaleItemInterface $item): void
    {
    }

    private function checkBundleSlots(SaleItemInterface $item, ?array $exclude): void
    {
        $product = $this->resolve($item);

        if (!ProductTypes::isBundledType($product)) {
            return;
        }

        $slots = $this->filter->getBundleSlots($product);

        if (empty($slots)) {
            foreach ($item->getChildren() as $child) {
                if ($child->hasDatum(ItemBuilder::OPTION_GROUP_ID)) {
                    // Skip if item is linked to an option
                    continue;
                }

                if ($child->hasDatum(ItemBuilder::BUNDLE_SLOT_ID)) {
                    // Unexpected item child
                    throw new InvalidSaleItemException();
                }
            }

            return;
        }

        $validatedSlots = [];
        foreach ($item->getChildren() as $child) {
            if ($child->hasDatum(ItemBuilder::OPTION_GROUP_ID)) {
                // Skip if item is linked to an option
                continue;
            }

            // Don't check ItemBuilder::COMPONENT_ID datum, as bundles do not have component

            if (!$child->hasDatum(ItemBuilder::BUNDLE_SLOT_ID)) {
                // Unexpected item child
                throw new InvalidSaleItemException();
            }

            // Bundle slot lookup
            $slotId = $child->getDatum(ItemBuilder::BUNDLE_SLOT_ID);
            foreach ($slots as $slot) {
                if ($slotId !== $slot->getId()) {
                    continue;
                }
                // Bundle slot found

                $choices = $this->filter->getSlotChoices($slot);
                if (empty($choices)) {
                    // Obsolete bundle choice
                    throw new InvalidSaleItemException();
                }

                // Bundle slot choice lookup
                $choiceId = $child->getDatum(ItemBuilder::BUNDLE_CHOICE_ID);
                foreach ($choices as $choice) {
                    if ($choiceId !== $choice->getId()) {
                        continue;
                    }
                    // Bundle slot choice found

                    $this->checkBundleChoice($child, $choice, $exclude);

                    $validatedSlots[] = $slotId;

                    // Bundle choice found and valid: go to next sale item child
                    continue 3;
                }
            }

            // Obsolete bundle slot/choice
            throw new InvalidSaleItemException();
        }

        // Check if a required bundle slot is missing
        foreach ($slots as $slot) {
            if (!$slot->isRequired()) {
                continue;
            }

            if (in_array($slot->getId(), $validatedSlots, true)) {
                continue;
            }

            // Missing required bundle slot
            throw new InvalidSaleItemException();
        }
    }

    private function checkBundleChoice(SaleItemInterface $item, BundleChoiceInterface $choice, ?array $exclude): void
    {
        // Check whether sale item's product matches slot choice's one
        if ($choice->getProduct() !== $this->resolve($item)) {
            // Bundle choice product mismatch
            throw new InvalidSaleItemException();
        }

        // Check quantities
        if ($item->getQuantity() < $choice->getMinQuantity()) {
            // Bundle choice quantity mismatch
            throw new InvalidSaleItemException();
        } elseif ($item->getQuantity() > $choice->getMaxQuantity()) {
            // Bundle choice quantity mismatch
            throw new InvalidSaleItemException();
        }

        if (!empty($exclude)) {
            $exclude = array_unique(array_merge($exclude, $choice->getExcludedOptionGroups()));
        } else {
            $exclude = $choice->getExcludedOptionGroups();
        }

        $this->checkItem($item, $exclude);
    }

    private function checkComponents(SaleItemInterface $item): void
    {
        $product = $this->resolve($item);

        if (!ProductTypes::isVariantType($product)) {
            return;
        }

        $variable = $product->getParent();

        if (empty($components = $variable->getComponents())) {
            foreach ($item->getChildren() as $child) {
                if ($child->hasDatum(ItemBuilder::OPTION_GROUP_ID)) {
                    // Skip if item is linked to an option
                    continue;
                }

                if ($child->hasDatum(ItemBuilder::COMPONENT_ID)) {
                    // Unexpected component
                    throw new InvalidSaleItemException();
                }
            }

            return;
        }

        // Component lookup
        $validatedComponents = [];
        foreach ($item->getChildren() as $child) {
            if ($child->hasDatum(ItemBuilder::OPTION_GROUP_ID)) {
                // Skip if item is linked to an option
                continue;
            }

            if (!$child->hasDatum(ItemBuilder::COMPONENT_ID)) {
                // Unexpected component
                throw new InvalidSaleItemException();
            }

            // Component lookup
            $componentId = $item->getDatum(ItemBuilder::COMPONENT_ID);
            foreach ($components as $component) {
                if ($componentId !== $component->getId()) {
                    continue;
                }

                $this->checkComponent($child, $component);

                $validatedComponents[] = $componentId;

                continue 2; // Component found and valid: got to next item child
            }

            // Obsolete component
            throw new InvalidSaleItemException();
        }

        // Check for missing component
        foreach ($components as $component) {
            if (in_array($component->getId(), $validatedComponents, true)) {
                continue;
            }

            // Missing component
            throw new InvalidSaleItemException();
        }
    }

    private function checkComponent(SaleItemInterface $item, Component $component): void
    {
        if ($component->getChild() !== $this->resolve($item)) {
            // Component product mismatch
            throw new InvalidSaleItemException();
        }

        if (!$component->getQuantity()->equals($item->getQuantity())) {
            // Component quantity mismatch
            throw new InvalidSaleItemException();
        }
    }

    private function checkOptions(SaleItemInterface $item, array $exclude): void
    {
        $groups = $this->getOptionGroups($item, $exclude);

        if (empty($groups)) {
            foreach ($item->getChildren() as $child) {
                if (!$child->hasDatum(ItemBuilder::OPTION_GROUP_ID)) {
                    continue;
                }

                // Unexpected option
                throw new InvalidSaleItemException();
            }

            return;
        }

        $validatedGroups = [];
        foreach ($item->getChildren() as $child) {
            if (!$child->hasDatum(ItemBuilder::OPTION_GROUP_ID)) {
                continue;
            }

            $groupId = $child->getDatum(ItemBuilder::OPTION_GROUP_ID);
            foreach ($groups as $group) {
                if ($group->getId() !== $groupId) {
                    continue;
                }

                $options = $this->filter->getGroupOptions($group);
                if (empty($options)) {
                    // Obsolete option group
                    throw new InvalidSaleItemException();
                }

                $optionId = $child->getDatum(ItemBuilder::OPTION_ID);
                foreach ($options as $option) {
                    if ($option->getId() !== $optionId) {
                        continue;
                    }

                    $this->checkOption($child, $option);

                    $validatedGroups[] = $group->getId();

                    continue 3; // Option found. Go to next item child
                }
            }

            // Obsolete option group/option
            throw new InvalidSaleItemException();
        }

        // Check if a required bundle slot is missing
        foreach ($groups as $group) {
            if (!$group->isRequired()) {
                continue;
            }

            if (in_array($group->getId(), $validatedGroups, true)) {
                continue;
            }

            // Missing required option group
            throw new InvalidSaleItemException();
        }
    }

    private function checkOption(SaleItemInterface $item, OptionInterface $option): void
    {
        if (null !== $product = $option->getProduct()) {
            if ($product !== $this->resolve($item)) {
                throw new InvalidSaleItemException();
            }

            $this->checkItem($item, null);

            return;
        }

        if ($item->getReference() !== $option->getReference()) {
            throw new InvalidSaleItemException();
        }
    }

    private function resolve(SaleItemInterface $item): ProductInterface
    {
        $product = $this->subjectHelper->resolve($item);

        if (!$product instanceof ProductInterface) {
            throw new UnexpectedTypeException($product, ProductInterface::class);
        }

        return $product;
    }

    /**
     * Returns the available option groups for the given sale item (merges variable and variant groups).
     *
     * @param SaleItemInterface $item
     * @param array             $exclude = The option group ids to exclude
     *
     * @return OptionGroupInterface[]
     */
    private function getOptionGroups(SaleItemInterface $item, array $exclude = []): array
    {
        $product = $this->resolve($item);

        $groups = $this->filter->getOptionGroups($product, $exclude);

        // Filter product option groups
        if ($product->getType() === ProductTypes::TYPE_VARIANT) {
            $variable = $product->getParent();

            // Filter variant option groups
            $groups = array_merge($this->filter->getOptionGroups($variable, $exclude), $groups);
        }

        return $groups;
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Form\EventListener\SaleItem;

use Ekyna\Bundle\ProductBundle\Exception\LogicException;
use Ekyna\Bundle\ProductBundle\Form\Type\SaleItem\OptionGroupType;
use Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Class OptionsGroupsListener
 * @package Ekyna\Bundle\ProductBundle\Form\EventListener\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionsGroupsListener implements EventSubscriberInterface
{
    /**
     * @var ItemBuilder
     */
    private $itemBuilder;


    /**
     * Constructor.
     *
     * @param ItemBuilder $itemBuilder
     */
    public function __construct(ItemBuilder $itemBuilder)
    {
        $this->itemBuilder = $itemBuilder;
    }

    /**
     * Pre set data event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        // Event data : Model data (doctrine collection of sale items)

        $this->buildForm($event->getForm());
    }

    /**
     * Pre submit event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        // Event data : Request data (associative array)

        $this->buildForm($event->getForm());
    }

    /**
     * Builds the tree map.
     *
     * @param SaleItemInterface $item
     *
     * @return array
     */
    private function buildTreeMap(SaleItemInterface $item)
    {
        $groups = $this->itemBuilder->getOptionGroups($item);

        $groupIds = array_map(function (OptionGroupInterface $group) {
            return $group->getId();
        }, $groups);

        $map = [
            'ids'      => $groupIds,
            'groups'   => $this->itemBuilder->getOptionGroups($item),
            'children' => [],
        ];

        $product = $this->itemBuilder->getProvider()->resolve($item);

        if ($product->getType() === ProductTypes::TYPE_BUNDLE) {
            foreach ($product->getBundleSlots() as $slot) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $bundleChoice */
                $bundleChoice = $slot->getChoices()->first();
                if (!$bundleChoice->isUseOptions()) {
                    continue;
                }

                foreach ($item->getChildren() as $index => $child) {
                    if (!$child->hasData(ItemBuilder::BUNDLE_SLOT_ID)) {
                        continue;
                    }

                    if ($slot->getId() === $child->getData(ItemBuilder::BUNDLE_SLOT_ID)) {
                        $childMap = $this->buildTreeMap($child);

                        // Skip when no groups or children
                        if (empty($childMap['groups']) && empty($childMap['children'])) {
                            continue 2;
                        }

                        $childMap['slot_id'] = $slot->getId();

                        $map['children'][] = $childMap;

                        continue 2;
                    }

                    //throw new LogicException("Item children / Option groups miss match.");
                }
            }
        }

        return $map;
    }

    /**
     * Removes unexpected child items.
     *
     * @param SaleItemInterface $item
     * @param array             $treeMap
     */
    private function clearItems(SaleItemInterface $item, array $treeMap)
    {
        $ids = $treeMap['ids'];

        // Remove item children that does not match option groups
        foreach ($item->getChildren() as $childItem) {
            // Skip non option group child
            if (!$childItem->hasData(ItemBuilder::OPTION_GROUP_ID)) {
                continue;
            }

            $id = intval($childItem->getData(ItemBuilder::OPTION_GROUP_ID));
            if (0 < $id && in_array($id, $ids)) {
                continue;
            }

            $item->removeChild($childItem);
        }

        // Recurse for children
        foreach ($treeMap['children'] as $childMap) {
            foreach ($item->getChildren() as $childItem) {
                // Skip non bundle slot child
                if (!$childItem->hasData(ItemBuilder::BUNDLE_SLOT_ID)) {
                    continue;
                }

                if ($childMap['slot_id'] == $childItem->getData(ItemBuilder::BUNDLE_SLOT_ID)) {
                    $this->clearItems($childItem, $childMap);
                }
            }
        }
    }

    /**
     * Creates expected child items.
     *
     * @param SaleItemInterface $item
     * @param array             $treeMap
     */
    private function createItems(SaleItemInterface $item, array $treeMap)
    {
        $groups = $treeMap['groups'];

        // Creates missing item children
        /** @var \Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface $group */
        foreach ($groups as $group) {
            // Find matching sale item's child
            foreach ($item->getChildren() as $childItem) {
                if ($group->getId() === $childItem->getData(ItemBuilder::OPTION_GROUP_ID)) {
                    continue 2;
                }
            }

            // Not found => initialize it
            $childItem = $item->createChild();

            $this->itemBuilder->initializeFromOptionGroup($childItem, $group);
        }

        // Recurse for children
        foreach ($treeMap['children'] as $childMap) {
            foreach ($item->getChildren() as $childItem) {
                // Skip non bundle slot child
                if (!$childItem->hasData(ItemBuilder::BUNDLE_SLOT_ID)) {
                    continue;
                }

                if ($childMap['slot_id'] == $childItem->getData(ItemBuilder::BUNDLE_SLOT_ID)) {
                    $this->createItems($childItem, $childMap);
                }
            }
        }
    }

    /**
     * Builds the flat map from the tree map.
     *
     * @param SaleItemInterface $item
     * @param array             $treeMap
     * @param array             $flatMap
     * @param array             $indexes
     */
    private function buildFlatMap(SaleItemInterface $item, array $treeMap, array &$flatMap, array $indexes = [])
    {
        /** @var \Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface $optionGroup */
        foreach ($treeMap['groups'] as $optionGroup) {
            foreach ($item->getChildren() as $index => $child) {
                if ($optionGroup->getId() === $child->getData(ItemBuilder::OPTION_GROUP_ID)) {
                    $path = '[' . implode('].children[', array_merge($indexes, [$index])) . ']';

                    $flatMap[$path] = $optionGroup;

                    continue 2;
                }
            }

            throw new LogicException("Item children / Option groups miss match.");
        }

        // Recurse for children
        foreach ($treeMap['children'] as $childMap) {
            foreach ($item->getChildren() as $index => $childItem) {
                // Skip non bundle slot child
                if (!$childItem->hasData(ItemBuilder::BUNDLE_SLOT_ID)) {
                    continue;
                }

                if ($childMap['slot_id'] == $childItem->getData(ItemBuilder::BUNDLE_SLOT_ID)) {
                    $this->buildFlatMap($childItem, $childMap, $flatMap, array_merge($indexes, [$index]));
                }
            }
        }
    }

    /**
     * Clears unexpected child forms.
     *
     * @param FormInterface $form
     * @param array         $flatMap
     */
    private function clearForms(FormInterface $form, array $flatMap)
    {
        $groupIds = array_map(function (OptionGroupInterface $group) {
            return $group->getId();
        }, $flatMap);

        // Remove form children that does not match option groups
        foreach ($form as $name => $child) {
            if (preg_match('~^option_group_([\d]+)$~', $name, $matches)) {
                if (!in_array($matches[1], $groupIds)) {
                    $form->remove($name);
                }
            }
        }
    }

    /**
     * Creates expected child forms.
     *
     * @param FormInterface $form
     * @param array         $flatMap
     */
    private function createForms(FormInterface $form, array $flatMap)
    {
        /** @var \Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface $optionGroup */
        foreach ($flatMap as $propertyPath => $optionGroup) {
            $name = 'option_group_' . $optionGroup->getId();
            if ($form->has($name)) {
                continue;
            }

            // Find matching sale item's child
            $form->add($name, OptionGroupType::class, [
                'label'         => $optionGroup->getTitle(),
                'property_path' => $propertyPath,
                'option_group'  => $optionGroup,
            ]);
        }
    }

    /**
     * Builds the option groups forms.
     *
     * @param FormInterface $form
     */
    private function buildForm(FormInterface $form)
    {
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        if (null === $item = $form->getParent()->getData()) {
            return;
        }

        $treeMap = $this->buildTreeMap($item);

        $this->clearItems($item, $treeMap);
        $this->createItems($item, $treeMap);

        $flatMap = [];
        $this->buildFlatMap($item, $treeMap, $flatMap);

        $this->clearForms($form, $flatMap);
        $this->createForms($form, $flatMap);
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT   => 'onPreSubmit',
        ];
    }
}

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
     * @var array
     */
    private $exclude;

    /**
     * Constructor.
     *
     * @param ItemBuilder $itemBuilder
     * @param array       $exclude The option groups ids to exclude
     */
    public function __construct(ItemBuilder $itemBuilder, array $exclude)
    {
        $this->itemBuilder = $itemBuilder;
        $this->exclude = $exclude;
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
        $this->buildForm($event->getForm(), $event->getData());
    }

    /**
     * Builds the tree map.
     *
     * @param SaleItemInterface $item
     * @param array             $data The submitted data
     * @param bool              $cascade
     *
     * @return array
     */
    private function buildTreeMap(SaleItemInterface $item, array $data = null, bool $cascade = true)
    {
        $groups = $this->itemBuilder->getOptionGroups($item, $this->exclude);

        $groupIds = array_map(function (OptionGroupInterface $group) {
            return $group->getId();
        }, $groups);

        $map = [
            'ids'      => $groupIds,
            'groups'   => $groups,
            'children' => [],
            'slot_id'  => null,
            'group_id' => null,
        ];

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
        $product = $this->itemBuilder->getProvider()->resolve($item);

        foreach ($item->getChildren() as $index => $child) {
            // Bundle slot lookup
            if (0 < $slotId = intval($child->getData(ItemBuilder::BUNDLE_SLOT_ID))) {
                if ($product->getType() === ProductTypes::TYPE_BUNDLE) {
                    $childMap = $this->buildTreeMap($child, $data);

                    // Add map if groups or children
                    if (!empty($childMap['groups']) || !empty($childMap['children'])) {
                        $childMap['slot_id'] = $slotId;
                        $map['children'][] = $childMap;

                        continue; // TODO Really ?
                    }
                }
            }

            // Option group lookup
            if (0 < $groupId = intval($child->getData(ItemBuilder::OPTION_GROUP_ID))) {
                $optionId = intval($child->getData(ItemBuilder::OPTION_ID));
                if (isset($data['option_group_' . $groupId])) {
                    $optionId = intval($data['option_group_' . $groupId]['choice']);
                }
                if (0 >= $optionId) {
                    continue;
                }

                $found = false;
                foreach ($groups as $group) {
                    if ($group->getId() != $groupId) {
                        continue;
                    }

                    // Skip if group has no options
                    $options = $this->itemBuilder->getFilter()->getGroupOptions($group);
                    if (empty($options)) {
                        continue; // TODO Should never happen ?
                    }

                    foreach ($options as $option) {
                        if ($option->getId() != $optionId) {
                            continue;
                        }

                        $found = true;

                        if ($cascade && $option->isCascade() && !is_null($p = $option->getProduct())) {
                            $this->itemBuilder->buildFromOption($child, $option, count($options));

                            $childMap = $this->buildTreeMap($child, $data, false);

                            // Add map if groups or children
                            if (!empty($childMap['groups']) || !empty($childMap['children'])) {
                                $childMap['group_id'] = $groupId;
                                $map['children'][] = $childMap;
                            }
                        }
                    }
                }

                if (!$found) {
                    throw new LogicException("Option not found.");
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
                // Bundle choice options
                if (isset($childMap['slot_id'])) {
                    // Skip non bundle slot child
                    if (!$childItem->hasData(ItemBuilder::BUNDLE_SLOT_ID)) {
                        continue; // Next child item
                    }

                    if ($childMap['slot_id'] == $childItem->getData(ItemBuilder::BUNDLE_SLOT_ID)) {
                        $this->clearItems($childItem, $childMap);

                        continue 2; // Next child map
                    }
                }

                // Option product options
                if (isset($childMap['group_id'])) {
                    // Skip non option group child
                    if (!$childItem->hasData(ItemBuilder::OPTION_GROUP_ID)) {
                        continue; // Next child item
                    }

                    if ($childMap['group_id'] == $childItem->getData(ItemBuilder::OPTION_GROUP_ID)) {
                        $this->clearItems($childItem, $childMap);

                        continue 2; // Next child map
                    }
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
        /** @var OptionGroupInterface $group */
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
                // Bundle choice options
                if (isset($childMap['slot_id'])) {
                    // Skip non bundle slot child
                    if (!$childItem->hasData(ItemBuilder::BUNDLE_SLOT_ID)) {
                        continue; // Next child item
                    }

                    if ($childMap['slot_id'] == $childItem->getData(ItemBuilder::BUNDLE_SLOT_ID)) {
                        $this->createItems($childItem, $childMap);

                        continue 2; // Next child map
                    }
                }

                // Option product options
                if (isset($childMap['group_id'])) {
                    // Skip non option group child
                    if (!$childItem->hasData(ItemBuilder::OPTION_GROUP_ID)) {
                        continue; // Next child item
                    }

                    if ($childMap['group_id'] == $childItem->getData(ItemBuilder::OPTION_GROUP_ID)) {
                        $this->createItems($childItem, $childMap);

                        continue 2; // Next child map
                    }
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
        /** @var OptionGroupInterface $optionGroup */
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
                // Bundle choice options
                if (isset($childMap['slot_id'])) {
                    // Skip non bundle slot child
                    if (!$childItem->hasData(ItemBuilder::BUNDLE_SLOT_ID)) {
                        continue; // Next child item
                    }

                    if ($childMap['slot_id'] == $childItem->getData(ItemBuilder::BUNDLE_SLOT_ID)) {
                        $this->buildFlatMap($childItem, $childMap, $flatMap, array_merge($indexes, [$index]));

                        continue 2; // Next child map
                    }
                }

                // Option product options
                if (isset($childMap['group_id'])) {
                    // Skip non option group child
                    if (!$childItem->hasData(ItemBuilder::OPTION_GROUP_ID)) {
                        continue; // Next child item
                    }

                    if ($childMap['group_id'] == $childItem->getData(ItemBuilder::OPTION_GROUP_ID)) {
                        $this->buildFlatMap($childItem, $childMap, $flatMap, array_merge($indexes, [$index]));

                        continue 2; // Next child map
                    }
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
        // We need to revert the order of the form's children so that
        // fields for cascaded options are submitted first.

        $removed = [];

        // 1. Remove children but keep references.
        /** @var OptionGroupInterface $optionGroup */
        foreach ($flatMap as $propertyPath => $optionGroup) {
            $name = 'option_group_' . $optionGroup->getId();
            if (!$form->has($name)) {
                continue;
            }

            $removed[$name] = $form->get($name);

            $form->remove($name);
        }

        // 2. (Re)Add in reversed order.
        /** @var OptionGroupInterface $optionGroup */
        foreach (array_reverse($flatMap, true) as $propertyPath => $optionGroup) {
            $name = 'option_group_' . $optionGroup->getId();
            if ($form->has($name)) {
                continue;
            }

            if (isset($removed[$name])) {
                $form->add($removed[$name]);
                unset($removed[$name]);
                continue;
            }

            $form->add($name, OptionGroupType::class, [
                'label'           => $optionGroup->getTitle(),
                'property_path'   => $propertyPath,
                'option_group'    => $optionGroup,
                'exclude_options' => $this->exclude,
            ]);
        }
    }

    /**
     * Builds the option groups forms.
     *
     * @param FormInterface $form
     * @param array         $data The submitted data
     */
    private function buildForm(FormInterface $form, array $data = null)
    {
        /** @var SaleItemInterface $item */
        if (null === $item = $form->getParent()->getData()) {
            return;
        }

        $treeMap = $this->buildTreeMap($item, $data);

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

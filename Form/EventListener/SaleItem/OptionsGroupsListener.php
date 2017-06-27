<?php

namespace Ekyna\Bundle\ProductBundle\Form\EventListener\SaleItem;

use Ekyna\Bundle\ProductBundle\Exception\LogicException;
use Ekyna\Bundle\ProductBundle\Form\Type\SaleItem\OptionGroupType;
use Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
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
     * Post submit event handler.
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        // Event data : Normalized data (doctrine collection of sale items)

        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        if (null === $item = $event->getForm()->getParent()->getData()) {
            return;
        }

        $optionsGroups = $this->itemBuilder->getOptionGroups($item);

        // Remove item children that do not match option groups
        foreach ($item->getChildren() as $index => $child) {
            // Skip non option group children
            if (!$child->hasData(ItemBuilder::OPTION_GROUP_ID)) {
                continue;
            }

            $optionGroupId = intval($child->getData(ItemBuilder::OPTION_GROUP_ID));
            if (0 < $optionGroupId) {
                // Finds the matching option group
                foreach ($optionsGroups as $optionGroup) {
                    if ($optionGroupId == $optionGroup->getId()) {
                        // Group found => find the matching option
                        $optionId = intval($child->getData(ItemBuilder::OPTION_ID));
                        if (0 < $optionId) {
                            foreach ($optionGroup->getOptions() as $option) {
                                if ($optionId == $option->getId()) {
                                    // Option found => next item child

                                    // TODO build item from option ? (currently done by the OptionGroupType)

                                    continue 3;
                                }
                            }
                        }

                        // Option not found => skip if group is required
                        if ($optionGroup->isRequired()) {
                            continue 2;
                        }
                    }
                }
            }

            // Group not found
            // or Option not found and Group not required
            $item->removeChild($child);
        }

        $event->setData($item->getChildren());
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

        $optionsGroups = $this->itemBuilder->getOptionGroups($item);

        $optionsGroupIds = array_map(function(OptionGroupInterface $group) {
            return $group->getId();
        }, $optionsGroups);

        // Remove item children that does not match option groups
        foreach ($item->getChildren() as $index => $child) {
            if (!$child->hasData(ItemBuilder::OPTION_GROUP_ID)) {
                continue;
            }

            $optionGroupId = intval($child->getData(ItemBuilder::OPTION_GROUP_ID));
            if (0 < $optionGroupId && in_array($optionGroupId, $optionsGroupIds)) {
                continue;
            }

            $item->removeChild($child);
        }

        // Creates missing item children
        /** @var \Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface $optionGroup */
        foreach ($optionsGroups as $optionGroup) {
            // Find matching sale item's child
            foreach ($item->getChildren() as $index => $child) {
                if ($optionGroup->getId() === $child->getData(ItemBuilder::OPTION_GROUP_ID)) {
                    continue 2;
                }
            }

            // Not found => initialize it
            $child = $item->createChild();

            $this->itemBuilder->initializeFromOptionGroup($child, $optionGroup);
        }

        // Remove form children that does not match option groups
        foreach ($form as $name => $child) {
            if (preg_match('~^option_group_([\d]+)$~', $name, $matches)) {
                if (!in_array($matches[1], $optionsGroupIds)) {
                    $form->remove($name);
                }
            }
        }

        // Creates missing form children
        /** @var \Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface $optionGroup */
        foreach ($optionsGroups as $optionGroup) {
            $name = 'option_group_' . $optionGroup->getId();
            if ($form->has($name)) {
                continue;
            }

            // Find matching sale item's child
            foreach ($item->getChildren() as $index => $child) {
                if ($optionGroup->getId() === $child->getData(ItemBuilder::OPTION_GROUP_ID)) {
                    $form->add($name, OptionGroupType::class, [
                        'label'         => $optionGroup->getTitle(),
                        'property_path' => '[' . $index . ']',
                        'option_group'  => $optionGroup,
                    ]);

                    continue 2;
                }
            }

            throw new LogicException("Item children / Option groups miss match.");
        }
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT   => 'onPreSubmit',
            FormEvents::POST_SUBMIT  => 'onPostSubmit',
        ];
    }
}

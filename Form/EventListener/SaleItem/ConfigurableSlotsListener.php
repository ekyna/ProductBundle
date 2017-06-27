<?php

namespace Ekyna\Bundle\ProductBundle\Form\EventListener\SaleItem;

use Ekyna\Bundle\ProductBundle\Exception\LogicException;
use Ekyna\Bundle\ProductBundle\Form\Type\SaleItem\ConfigurableSlotType;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Class ConfigurableSlotsListener
 * @package Ekyna\Bundle\ProductBundle\Form\EventListener\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigurableSlotsListener implements EventSubscriberInterface
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
        $this->buildForm($event->getForm());
    }

    /**
     * Pre submit event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $this->buildForm($event->getForm());
    }

    /**
     * Post submit event handler.
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
//        // TODO Should be done by the ConfigurableSlotType
//
//        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
//        $item = $event->getForm()->getParent()->getData();
//        $product = $this->productProvider->resolve($item);
//
//        $this
//            ->productProvider
//            ->getItemBuilder()
//            ->buildFromProduct($item, $product);
//
//        $event->setData($item);
    }

    /**
     * Builds the configurable slots forms.
     *
     * @param FormInterface $form
     */
    private function buildForm(FormInterface $form)
    {
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        if (null === $item = $form->getParent()->getData()) {
            return;
        }

        $bundleSlots = $this->itemBuilder->getBundleSlots($item);

        foreach ($bundleSlots as $bundleSlot) {
            foreach ($item->getChildren() as $index => $child) {
                $bundleSlotId = intval($child->getData(ItemBuilder::BUNDLE_SLOT_ID));
                if ($bundleSlotId == $bundleSlot->getId()) {
                    $form->add('slot_' . $bundleSlot->getId(), ConfigurableSlotType::class, [
                        'bundle_slot'   => $bundleSlot,
                        'property_path' => '[' . $index . ']',
                    ]);
                    continue 2;
                }
            }

            // TODO Use ItemBuilder initialize* method
            throw new LogicException(sprintf(
                "Sale item was not found for bundle slot #%s.\n" .
                "You must call ItemBuilder::initializeItem() first.",
                $bundleSlot->getId()
            ));
        }
    }

    /**
     *
     * @return array
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

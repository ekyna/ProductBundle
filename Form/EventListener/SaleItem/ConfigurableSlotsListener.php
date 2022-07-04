<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\EventListener\SaleItem;

use Ekyna\Bundle\ProductBundle\Exception\LogicException;
use Ekyna\Bundle\ProductBundle\Form\Type\SaleItem\ConfigurableSlotType;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
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
    private ItemBuilder $itemBuilder;

    public function __construct(ItemBuilder $itemBuilder)
    {
        $this->itemBuilder = $itemBuilder;
    }

    /**
     * Pre set data event handler.
     */
    public function onPreSetData(FormEvent $event): void
    {
        $this->buildForm($event->getForm());
    }

    /**
     * Builds the configurable slots forms.
     */
    private function buildForm(FormInterface $form): void
    {
        /** @var SaleItemInterface $item */
        if (null === $item = $form->getParent()->getData()) {
            return;
        }

        $bundleSlots = $this->itemBuilder->getBundleSlots($item);

        foreach ($bundleSlots as $bundleSlot) {
            foreach ($item->getChildren() as $index => $child) {
                $bundleSlotId = intval($child->getDatum(ItemBuilder::BUNDLE_SLOT_ID));
                if ($bundleSlotId === $bundleSlot->getId()) {
                    $form->add('slot_' . $bundleSlot->getId(), ConfigurableSlotType::class, [
                        'bundle_slot'   => $bundleSlot,
                        'property_path' => '[' . $index . ']',
                    ]);
                    continue 2;
                }
            }

            throw new LogicException(sprintf(
                "Sale item was not found for bundle slot #%s.\n" .
                'You must call ItemBuilder::initializeItem() first.',
                $bundleSlot->getId()
            ));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }
}

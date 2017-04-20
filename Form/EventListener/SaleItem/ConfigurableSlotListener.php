<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\EventListener\SaleItem;

use Ekyna\Bundle\ProductBundle\Form\DataTransformer\IdToChoiceObjectTransformer;
use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Service\Commerce\FormBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class ConfigurableSlotListener
 * @package Ekyna\Bundle\ProductBundle\Form\EventListener\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigurableSlotListener implements EventSubscriberInterface
{
    private ItemBuilder                 $itemBuilder;
    private FormBuilder                 $formBuilder;
    private IdToChoiceObjectTransformer $transformer;

    public function __construct(
        ItemBuilder                 $itemBuilder,
        FormBuilder                 $formBuilder,
        IdToChoiceObjectTransformer $transformer
    ) {
        $this->itemBuilder = $itemBuilder;
        $this->formBuilder = $formBuilder;
        $this->transformer = $transformer;
    }

    /**
     * Pre set data event handler.
     */
    public function onPreSetData(FormEvent $event): void
    {
        /** @var SaleItemInterface $item */
        if (null === $item = $event->getData()) {
            return;
        }

        $form = $event->getForm();

        $choiceId = $item->getData(ItemBuilder::BUNDLE_CHOICE_ID);

        /** @var BundleChoiceInterface $choice */
        if (null !== $choice = $this->transformer->transform($choiceId)) {
            $this->formBuilder->buildBundleChoiceForm($form, $choice);
        } else {
            $this->formBuilder->clearBundleChoiceForm($form);
        }
    }

    /**
     * Pre submit event handler.
     */
    public function onPreSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $item = $form->getData();

        $this->formBuilder->clearBundleChoiceForm($form);

        $oldChoiceId = $form->get('choice')->getData();

        // Choice field's data is not ready (pre submit has not been called on the child form)
        // So we fetch the choice id from this form's event data.
        $choiceId = $event->getData()['choice'];

        if ($oldChoiceId != $choiceId) {
            $this->formBuilder->clearBundleChoiceForm($form);
        }

        /** @var BundleChoiceInterface $choice */
        if (null !== $choice = $this->transformer->transform($choiceId)) {
            // Initialize the sale item from the bundle choice if it has changed
            if ($choice->getId() != $item->getData(ItemBuilder::BUNDLE_CHOICE_ID)) {
                $this->itemBuilder->initializeFromBundleChoice($item, $choice);
            }

            // (Re)Build the form
            $this->formBuilder->buildBundleChoiceForm($form, $choice);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT   => 'onPreSubmit',
        ];
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class ProductAttributesSlotType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttributesSlotType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\ProductAttributesSlot $slot */
            $slot = $event->getData();
            $form = $event->getForm();

            $form->add('attributes', ChoiceType::class, [
                'label' => $slot->getGroup()->getName(),
                'choices' => $slot->getGroup()->getAttributes()->toArray(),
            ]);
        });
    }

}

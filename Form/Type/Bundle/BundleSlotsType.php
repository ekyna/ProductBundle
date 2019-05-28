<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BundleSlotsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'configurable'    => false,
                'label'           => 'ekyna_product.bundle_slot.label.plural',
                'prototype_name'  => '__slot__',
                'sub_widget_col'  => 11,
                'button_col'      => 1,
                'allow_sort'      => true,
                'add_button_text' => function (Options $options) {
                    if ($options['configurable']) {
                        return 'ekyna_product.bundle_slot.button.add_configurable';
                    }

                    return 'ekyna_product.bundle_slot.button.add';
                },
                'entry_type'      => BundleSlotType::class,
                'entry_options'   => function (Options $options) {
                    return [
                        'configurable' => $options['configurable'],
                    ];
                },
            ])
            ->setAllowedTypes('configurable', 'bool');
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'product-bundle-slots');
        if ($options['configurable']) {
            FormUtil::addClass($view, 'configurable');
        }
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class BundleSlotsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'configurable'    => false,
                'label'           => t('bundle_slot.label.plural', [], 'EkynaProduct'),
                'prototype_name'  => '__slot__',
                'sub_widget_col'  => 11,
                'button_col'      => 1,
                'allow_sort'      => true,
                'add_button_text' => function (Options $options) {
                    if ($options['configurable']) {
                        return t('bundle_slot.button.add_configurable', [], 'EkynaProduct');
                    }

                    return t('bundle_slot.button.add', [], 'EkynaProduct');
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

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'product-bundle-slots');
        if ($options['configurable']) {
            FormUtil::addClass($view, 'configurable');
        }
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}

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
 * Class BundleChoicesType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoicesType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'configurable'    => false,
                'label'           => false,
                'prototype_name'  => '__choice__',
                'prototype_data'  => function (Options $options, $value) {
                    if (null !== $value) {
                        return $value;
                    }

                    return new $options['choice_class']();
                },
                'sub_widget_col'  => function (Options $options) {
                    return $options['configurable'] ? 11 : 12;
                },
                'button_col'      => function (Options $options) {
                    return $options['configurable'] ? 1 : 0;
                },
                'allow_add'       => function (Options $options) {
                    return $options['configurable'];
                },
                'add_button_text' => function (Options $options) {
                    return $options['configurable'] ? t('bundle_choice.button.add', [], 'EkynaProduct') : null;
                },
                'allow_sort'      => function (Options $options) {
                    return $options['configurable'];
                },
                'allow_delete'    => function (Options $options) {
                    return $options['configurable'];
                },
                'entry_type'      => BundleChoiceType::class,
                'entry_options'   => function (Options $options) {
                    return [
                        'configurable' => $options['configurable'],
                    ];
                },
            ])
            ->setRequired('choice_class')
            ->setAllowedTypes('configurable', 'bool')
            ->setAllowedTypes('choice_class', 'string');
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'product-bundle-choices');
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}

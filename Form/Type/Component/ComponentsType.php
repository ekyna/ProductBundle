<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Component;

use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class ComponentsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Component
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ComponentsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label'           => t('component.label.plural', [], 'EkynaProduct'),
                'prototype_name'  => '__component__',
                'sub_widget_col'  => 11,
                'button_col'      => 1,
                'allow_sort'      => false,
                'add_button_text' => t('component.button.add', [], 'EkynaProduct'),
                'entry_type'      => ComponentType::class,
                'required'        => false,
            ]);
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}

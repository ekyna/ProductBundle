<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Component;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ComponentsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Component
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ComponentsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'           => 'ekyna_product.component.label.plural',
                'prototype_name'  => '__component__',
                'sub_widget_col'  => 11,
                'button_col'      => 1,
                'allow_sort'      => false,
                'add_button_text' => 'ekyna_product.component.button.add',
                'entry_type'      => ComponentType::class,
                'required'        => false,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}

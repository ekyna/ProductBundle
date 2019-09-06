<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\CrossSelling;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CrossSellingsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\CrossSelling
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CrossSellingsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'           => 'ekyna_product.cross_selling.label.plural',
                'prototype_name'  => '__component__',
                'sub_widget_col'  => 10,
                'button_col'      => 2,
                'allow_sort'      => true,
                'add_button_text' => 'ekyna_product.cross_selling.button.add',
                'entry_type'      => CrossSellingType::class,
                'entry_options'   => ['collection' => true],
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

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\CrossSelling;

use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CrossSellingsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\CrossSelling
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CrossSellingsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label'           => t('cross_selling.label.plural', [], 'EkynaProduct'),
                'prototype_name'  => '__component__',
                'sub_widget_col'  => 10,
                'button_col'      => 2,
                'allow_sort'      => true,
                'add_button_text' => t('cross_selling.button.add', [], 'EkynaProduct'),
                'entry_type'      => CrossSellingType::class,
                'entry_options'   => ['collection' => true],
                'required'        => false,
            ]);
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HalfDualType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class HalfDualType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('slot_count', 3);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return SlotsType::class;
    }
}
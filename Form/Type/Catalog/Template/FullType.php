<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FullType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FullType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('slot_count', 1);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return SlotsType::class;
    }
}

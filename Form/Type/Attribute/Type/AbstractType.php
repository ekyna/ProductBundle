<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type;

use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Symfony\Component\Form\AbstractType as BaseType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractType extends BaseType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ProductAttributeInterface::class,
                'attribute'  => null,
            ])
            ->setAllowedTypes('attribute', AttributeInterface::class);
    }
}
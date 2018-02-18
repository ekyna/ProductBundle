<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TextAttributeType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TextAttributeType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('value', TextType::class, [
            'label'    => false,
            'required' => $options['required'],
            'attr'     => [
                'widget_col' => 12,
            ],
        ]);
    }
}
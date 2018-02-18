<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class BooleanAttributeType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BooleanAttributeType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('value', ChoiceType::class, [
            'label'   => false,
            'choices' => [
                'ekyna_core.value.yes' => '1',
                'ekyna_core.value.no'  => '0',
            ],
            'expanded' => true,
            'attr' => [
                'class' => 'inline',
            ]
        ]);
    }
}
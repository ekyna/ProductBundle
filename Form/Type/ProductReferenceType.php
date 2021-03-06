<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ProductReferenceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductReferenceType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label'       => 'ekyna_core.field.type',
                'choices'     => ProductReferenceTypes::getChoices(),
                'placeholder' => 'ekyna_core.field.type',
                'select2'     => false,
            ])
            ->add('code', TextType::class, [
                'label'  => 'ekyna_core.field.code',
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.code',
                ],
            ]);
    }

    public function getBlockPrefix()
    {
        return 'ekyna_product_reference';
    }
}

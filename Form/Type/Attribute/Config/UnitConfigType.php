<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config;

use Ekyna\Bundle\CommerceBundle\Model\Units;
use Ekyna\Bundle\ProductBundle\Validator\Constraints\UnitAttributeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UnitConfigType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnitConfigType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('unit', Type\ChoiceType::class, [
                'label'         => 'ekyna_commerce.unit.label',
                'choices'       => Units::getChoices(),
            ])
            ->add('suffix', Type\TextType::class, [
                'label'         => 'ekyna_product.attribute.config.suffix',
                'required'      => false,
                'attr'          => [
                    'help_text' => 'ekyna_product.attribute.help.suffix',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('constraints', [
            new UnitAttributeConfig(),
        ]);
    }
}
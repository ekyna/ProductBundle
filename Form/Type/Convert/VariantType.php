<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Convert;

use Ekyna\Bundle\ProductBundle\Form\Type\ProductAttributesType;
use Ekyna\Bundle\ProductBundle\Model\AttributeSetInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class VariantType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Convert
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('attributes', ProductAttributesType::class, [
            'label'         => t('attribute_choice.label.plural', [], 'EkynaProduct'),
            'attribute_set' => $options['attribute_set'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'attribute_set'     => null,
                'data_class'        => ProductInterface::class,
                'validation_groups' => ['convert_' . ProductTypes::TYPE_VARIANT],
            ])
            ->setAllowedTypes('attribute_set', ['null', AttributeSetInterface::class]);
    }
}

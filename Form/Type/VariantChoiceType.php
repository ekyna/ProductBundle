<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VariantChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantChoiceType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'         => 'ekyna_product.variant.label.singular',
                'property_path' => 'subjectData[variant]',
                'choices'       => function (Options $options, $value) {
                    if (empty($value)) {
                        $variable = $options['variable'];

                        /** @var Model\ProductInterface $variant */
                        foreach ($variable->getVariants() as $variant) {
                            $value[$variant->getTitle()] = $variant->getId();
                        }
                    }

                    return $value;
                },
                'attr'          => [
                    'class' => 'no-select2',
                ],
            ])
            ->setRequired(['variable'])
            ->setAllowedTypes('variable', 'Ekyna\Bundle\ProductBundle\Model\ProductInterface')
            ->setAllowedValues('variable', function (Model\ProductInterface $variable) {
                return $variable->getType() === Model\ProductTypes::TYPE_VARIABLE;
            });
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}

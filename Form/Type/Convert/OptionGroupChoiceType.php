<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Convert;

use Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OptionGroupChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Convert
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroupChoiceType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'        => 'ekyna_product.option_group.label.plural',
                'mapped'       => false,
                'optionGroups' => [],
                'choices'      => function (Options $options, $value) {
                    if (empty($value)) {
                        $optionGroups = $options['optionGroups'];
                        /** @var OptionGroupInterface $optionGroup */
                        foreach ($optionGroups as $optionGroup) {
                            $value[(string)$optionGroup] = $optionGroup->getId();
                        }
                    }

                    return $value;
                },
                'expanded'     => true,
                'multiple'     => true,
                'required'     => false,
            ])
            ->setAllowedTypes('optionGroups', 'array')
            ->setAllowedValues('optionGroups', function ($value) {
                if (empty($value)) {
                    return false;
                }

                foreach ($value as $optionGroup) {
                    if (!$optionGroup instanceof OptionGroupInterface) {
                        return false;
                    }
                }

                return true;
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

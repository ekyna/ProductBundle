<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute;

use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class AttributeSetChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSetChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'       => t('attribute_set.label.singular', [], 'EkynaProduct'),
            'resource'    => 'ekyna_product.attribute_set',
            'placeholder' => t('value.choose', [], 'EkynaUi'),
        ]);
    }

    public function getParent(): ?string
    {
        return ResourceChoiceType::class;
    }
}

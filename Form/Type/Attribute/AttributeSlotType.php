<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionPositionType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class AttributeSlotType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSlotType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('attribute', ResourceChoiceType::class, [
                'label'    => false,
                'resource' => 'ekyna_product.attribute',
                'attr'     => [
                    'widget_col' => 12,
                ],
            ])
            ->add('required', Type\CheckboxType::class, [
                'label'    => t('field.required', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('naming', Type\CheckboxType::class, [
                'label'    => t('attribute_slot.field.naming', [], 'EkynaProduct'),
                'required' => false,
                'attr'     => [
                    'help_text' => t('attribute_slot.help.naming', [], 'EkynaProduct'),
                ],
            ])
            ->add('position', CollectionPositionType::class);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_attribute_slot';
    }
}

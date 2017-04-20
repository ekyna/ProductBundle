<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class AttributeGroupType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSetType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => t('field.name', [], 'EkynaUi'),
            ])
            ->add('slots', CollectionType::class, [
                'label'          => t('attribute.label.plural', [], 'EkynaProduct'),
                'sub_widget_col' => 10,
                'button_col'     => 2,
                'allow_sort'     => true,
                'entry_type'     => AttributeSlotType::class,
            ]);
    }
}

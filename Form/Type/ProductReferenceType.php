<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ProductReferenceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductReferenceType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ConstantChoiceType::class, [
                'label'       => t('field.type', [], 'EkynaUi'),
                'class'       => ProductReferenceTypes::class,
                'placeholder' => t('field.type', [], 'EkynaUi'),
                'select2'     => false,
            ])
            ->add('code', TextType::class, [
                'label' => t('field.code', [], 'EkynaUi'),
                'attr'  => [
                    'placeholder' => t('field.code', [], 'EkynaUi'),
                ],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_reference';
    }
}

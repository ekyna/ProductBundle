<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Component;

use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\PriceType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductSearchType;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ComponentType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ComponentType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('child', ProductSearchType::class, [
                'types' => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIANT,
                    ProductTypes::TYPE_BUNDLE,
                ],
            ])
            ->add('quantity', NumberType::class, [
                'label'   => t('field.quantity', [], 'EkynaUi'),
                'decimal' => true,
                'scale'   => 3, // TODO Packaging format
            ])
            ->add('netPrice', PriceType::class, [
                'label'    => t('field.net_price', [], 'EkynaCommerce'),
                'required' => false,
            ]);
    }

    public function getBlockPrefix(): ?string
    {
        return 'ekyna_product_component';
    }
}

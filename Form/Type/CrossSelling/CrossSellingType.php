<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\CrossSelling;

use Ekyna\Bundle\ProductBundle\Form\Type\ProductSearchType;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionPositionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CrossSellingType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\CrossSelling
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CrossSellingType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['collection']) {
            $builder->add('position', CollectionPositionType::class);
        } else {
            $builder->add('source', ProductSearchType::class, [
                'label'    => t('cross_selling.field.source', [], 'EkynaProduct'),
                'required' => true,
                'types'    => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIABLE,
                    ProductTypes::TYPE_BUNDLE,
                    ProductTypes::TYPE_CONFIGURABLE,
                ],
            ]);
        }

        $builder->add('target', ProductSearchType::class, [
            'label'    => t('cross_selling.field.target', [], 'EkynaProduct'),
            'required' => true,
            'types'    => [
                ProductTypes::TYPE_SIMPLE,
                ProductTypes::TYPE_VARIABLE,
                ProductTypes::TYPE_BUNDLE,
                ProductTypes::TYPE_CONFIGURABLE,
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('collection', false);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_cross_selling';
    }
}

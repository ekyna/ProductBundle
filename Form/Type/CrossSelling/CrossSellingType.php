<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\CrossSelling;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionPositionType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductSearchType;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CrossSellingType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\CrossSelling
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CrossSellingType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['collection']) {
            $builder->add('position', CollectionPositionType::class);
        } else {
            $builder->add('source', ProductSearchType::class, [
                'label' => 'ekyna_product.cross_selling.field.source',
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
            'label' => 'ekyna_product.cross_selling.field.target',
            'required' => true,
            'types'    => [
                ProductTypes::TYPE_SIMPLE,
                ProductTypes::TYPE_VARIABLE,
                ProductTypes::TYPE_BUNDLE,
                ProductTypes::TYPE_CONFIGURABLE,
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('empty_data', new $this->dataClass)
            ->setDefault('collection', false);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_cross_selling';
    }
}

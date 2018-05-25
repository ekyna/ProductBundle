<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template;

use Ekyna\Bundle\ProductBundle\Entity\CatalogSlot;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductSearchType;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SlotType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SlotType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['product']) {
            $builder->add('product', ProductSearchType::class, [
                'required' => true,
                'types'    => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIABLE,
                    ProductTypes::TYPE_VARIANT,
                    ProductTypes::TYPE_BUNDLE,
                    ProductTypes::TYPE_CONFIGURABLE,
                ],
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'      => false,
            'compound'   => true,
            'product'    => null,
            'data_class' => CatalogSlot::class,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_catalog_slot';
    }
}

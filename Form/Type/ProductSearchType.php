<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\CoreBundle\Form\Type\EntitySearchType;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductSearchType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductSearchType extends AbstractType
{
    /**
     * @var string
     */
    private $productClass;


    /**
     * Constructor.
     *
     * @param string $class
     */
    public function __construct($class)
    {
        $this->productClass = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'    => 'ekyna_product.product.label.singular',
                'class'    => $this->productClass,
                'route'    => function(Options $options) {
                    return $options['admin_mode']
                        ? 'ekyna_product_product_admin_search'
                        : 'ekyna_product_account_product_search';
                },
                'required' => true,
                'visible'  => false,
                'types'    => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIABLE,
                    ProductTypes::TYPE_BUNDLE,
                    ProductTypes::TYPE_CONFIGURABLE,
                ],
            ])
            ->setAllowedTypes('visible', 'bool')
            ->setAllowedTypes('types', 'array')
            ->setNormalizer('route_params', function (Options $options, $value) {
                if (!isset($value['types'])) {
                    $value['types'] = $options['types'];
                }
                if (!isset($value['visible']) && $options['visible']) {
                    $value['visible'] = 1;
                }

                return $value;
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntitySearchType::class;
    }
}


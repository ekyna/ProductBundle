<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Product;

use Ekyna\Bundle\CoreBundle\Form\Type\EntitySearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductSearchType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Product
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
                'label'        => 'ekyna_product.product.label.singular',
                'class'        => $this->productClass,
                'required'     => true,
                'search_route' => 'ekyna_product_product_admin_search',
                'find_route'   => 'ekyna_product_product_admin_find',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntitySearchType::class;
    }
}


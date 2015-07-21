<?php

namespace Ekyna\Bundle\ProductBundle\DependencyInjection;

use Ekyna\Bundle\AdminBundle\DependencyInjection\AbstractExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EkynaProductExtension
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaProductExtension extends AbstractExtension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->configure($configs, 'ekyna_product', new Configuration(), $container);

        // Base abstract classes
        $container->setParameter('ekyna_product.option.class', $config['option_class']);
        $container->setParameter('ekyna_product.product.class', $config['product_class']);
        
        // Options classes map (CTI)
        $container->setParameter('ekyna_product.options_classes_map', array_map(function($option) {
        	return $option['class'];
        }, $config['options']));

        // Products classes map (CTI)
        $container->setParameter('ekyna_product.products_classes_map', array_map(function($product) {
        	return $product['class'];
        }, $config['products']));

        // Options configuration
        $container->setParameter('ekyna_product.options.configuration', $config['options']);

        // Products configuration
        $container->setParameter('ekyna_product.products.configuration', $config['products']);
    }
}

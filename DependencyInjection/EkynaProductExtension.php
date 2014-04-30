<?php

namespace Ekyna\Bundle\ProductBundle\DependencyInjection;

use Ekyna\Bundle\AdminBundle\DependencyInjection\AbstractExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * EkynaProductExtension
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaProductExtension extends AbstractExtension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        list($config, $loader) = $this->configure($configs, 'ekyna_product', new Configuration(), $container);

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

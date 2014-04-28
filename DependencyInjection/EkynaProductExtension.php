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

        $container->setParameter('ekyna_product.products_map', $config['products_map']);
    }
}

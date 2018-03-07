<?php

namespace Ekyna\Bundle\ProductBundle\DependencyInjection;

use Ekyna\Bundle\ResourceBundle\DependencyInjection\AbstractExtension;
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

        $container->setParameter('ekyna_product.default.no_image', $config['default']['no_image']);
        $container->setParameter('ekyna_product.default.sale_item_form_theme', $config['default']['sale_item_form_theme']);

        $cartSuccessListener = $container->getDefinition('ekyna_product.add_to_cart.event_subscriber');
        $cartSuccessListener->replaceArgument(1, $config['default']['cart_success_template']);
    }
}

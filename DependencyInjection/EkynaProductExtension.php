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

        $container->setParameter('ekyna_product.catalog_enabled', $config['catalog']['enabled']);
        $container->setParameter('ekyna_product.default.no_image', $config['default']['no_image']);
        $container->setParameter('ekyna_product.default.sale_item_form_theme', $config['default']['sale_item_form_theme']);

        $cartSuccessListener = $container->getDefinition('ekyna_product.add_to_cart.event_subscriber');
        $cartSuccessListener->replaceArgument(1, $config['default']['cart_success_template']);

        $editor = $config['editor'];
        foreach ($editor as $plugin => $c) {
            $container->setParameter('ekyna_product.editor.' . $plugin, $c);
        }

        $pricingRenderer = $container->getDefinition('ekyna_product.pricing.renderer');
        $pricingRenderer->replaceArgument(6, $config['pricing']);

        if ($config['catalog']['enabled']) {
            $catalogRegistry = $container->getDefinition('ekyna_product.catalog.registry');
            $catalogRegistry->replaceArgument(0, $config['catalog']['themes']);
            $catalogRegistry->replaceArgument(1, $config['catalog']['templates']);
        }
    }
}

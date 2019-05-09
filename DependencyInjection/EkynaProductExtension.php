<?php

namespace Ekyna\Bundle\ProductBundle\DependencyInjection;

use Ekyna\Bundle\ResourceBundle\DependencyInjection\AbstractExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EkynaProductExtension
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaProductExtension extends AbstractExtension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->configure($configs, 'ekyna_product', new Configuration(), $container);

        // Defaults
        $container->setParameter(
            'ekyna_product.default.no_image',
            $config['default']['no_image']
        );
        $container->setParameter(
            'ekyna_product.default.sale_item_form_theme',
            $config['default']['sale_item_form_theme']
        );
        $container->setParameter('ekyna_product.cache_ttl', $config['default']['cache_ttl']);

        $accountConfig = [
            'catalog' => $config['catalog']['enabled'] && $config['catalog']['account'],
        ];

        $container
            ->getDefinition('ekyna_product.add_to_cart.event_subscriber')
            ->replaceArgument(1, $config['default']['cart_success_template']);

        // Catalog
        $container->setParameter('ekyna_product.catalog_enabled', $config['catalog']['enabled']);
        if ($config['catalog']['enabled']) {
            $catalogRegistry = $container->getDefinition('ekyna_product.catalog.registry');
            $catalogRegistry->replaceArgument(0, $config['catalog']['themes']);
            $catalogRegistry->replaceArgument(1, $config['catalog']['templates']);
        }

        // Account menu event subscriber
        $container
            ->getDefinition('ekyna_product.account.menu_subscriber')
            ->replaceArgument(1, $accountConfig);

        // Account routing loader
        $container
            ->getDefinition('ekyna_product.routing.account_loader')
            ->replaceArgument(0, $accountConfig);

        // Editor
        $editor = $config['editor'];
        foreach ($editor as $plugin => $c) {
            $container->setParameter('ekyna_product.editor.' . $plugin, $c);
        }

        // Highlight
        $container
            ->getDefinition('ekyna_product.highlight')
            ->replaceArgument(6, $config['highlight']);

        // Pricing
        $container
            ->getDefinition('ekyna_product.pricing.renderer')
            ->replaceArgument(7, $config['pricing']);
    }
}

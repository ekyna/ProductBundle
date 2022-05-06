<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\DependencyInjection;

use Ekyna\Bundle\ProductBundle\Service\Features;
use Ekyna\Bundle\ResourceBundle\DependencyInjection\PrependBundleConfigTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

use function in_array;

/**
 * Class EkynaProductExtension
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaProductExtension extends Extension implements PrependExtensionInterface
{
    use PrependBundleConfigTrait;

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependBundleConfigFiles($container);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services/action.php');
        $loader->load('services/command.php');
        $loader->load('services/controller.php');
        $loader->load('services/factory.php');
        $loader->load('services/form.php');
        $loader->load('services/listener.php');
        $loader->load('services/migration.php');
        $loader->load('services/repository.php');
        $loader->load('services/serializer.php');
        $loader->load('services/show.php');
        $loader->load('services/table.php');
        $loader->load('services/twig.php');
        $loader->load('services/validator.php');
        $loader->load('services.php');

        if (in_array($container->getParameter('kernel.environment'), ['dev', 'test'], true)) {
            $loader->load('services/dev.php');
        }

        $this->configureFeatures($config['feature'], $container);

        // No image path
        $container->setParameter(
            'ekyna_product.default.no_image',
            $config['default']['no_image']
        );

        // Offer cache TTL
        $container
            ->getDefinition('ekyna_product.repository.offer')
            ->addMethodCall('setCacheTtl', [$config['default']['cache_ttl']]);

        // Price cache TTL
        $container
            ->getDefinition('ekyna_product.repository.price')
            ->addMethodCall('setCacheTtl', [$config['default']['cache_ttl']]);

        // Sale item form template
        $container
            ->getDefinition('ekyna_product.form_type_extension.sale_item_configure')
            ->replaceArgument(2, $config['default']['sale_item_form_theme']);

        // Add to cart success template
        $container
            ->getDefinition('ekyna_product.listener.add_to_cart')
            ->replaceArgument(1, $config['default']['cart_success_template']);

        // Catalog
        $container->setParameter('ekyna_product.catalog_enabled', $config['catalog']['enabled']);
        if ($config['catalog']['enabled']) {
            $catalogRegistry = $container->getDefinition('ekyna_product.registry.catalog');
            $catalogRegistry->replaceArgument(0, $config['catalog']['themes']);
            $catalogRegistry->replaceArgument(1, $config['catalog']['templates']);
        }

        // Account config
        $accountConfig = [
            'catalog' => $config['catalog']['enabled'] && $config['catalog']['account'],
        ];
        // Remove controller service
        if (!$accountConfig['catalog']) {
            $container->removeDefinition('ekyna_product.controller.account.catalog');
        }

        // Account menu event subscriber
        $container
            ->getDefinition('ekyna_product.listener.account.menu')
            ->replaceArgument(1, $accountConfig);

        // Account routing loader
        $container
            ->getDefinition('ekyna_product.loader.routing')
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
            ->getDefinition('ekyna_product.renderer.pricing')
            ->replaceArgument(6, $config['pricing']);
    }

    private function configureFeatures(array $config, ContainerBuilder $container): void
    {
        // Set features parameter
        $container->setParameter('ekyna_product.features', $config);

        // Set service config
        $container->getDefinition('ekyna_product.features')->replaceArgument(0, $config);

        // Gtin 13 generator
        if ($config[Features::GTIN13_GENERATOR]['enabled']) {
            $container
                ->register('ekyna_product.generator.gtin13', $config[Features::GTIN13_GENERATOR]['class'])
                ->setArguments([
                    $config[Features::GTIN13_GENERATOR]['path'],
                    '%kernel.debug%',
                ])
                ->addMethodCall('setManufacturerCode', [
                    $config[Features::GTIN13_GENERATOR]['manufacturer'],
                ]);

            $container
                ->getDefinition('ekyna_product.generator.external_reference')
                ->addMethodCall('setGtin13Generator', [
                    new Reference('ekyna_product.generator.gtin13'),
                ]);
        }
    }
}

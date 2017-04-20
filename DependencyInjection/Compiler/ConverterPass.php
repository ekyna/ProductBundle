<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\ProductBundle\Service\Converter\ConverterInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ConverterPass
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConverterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('ekyna_product.converter.product');

        foreach ($container->findTaggedServiceIds(ConverterInterface::DI_TAG, true) as $serviceId => $attributes) {
            $definition->addMethodCall('registerConverter', [new Reference($serviceId)]);
        }
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\ProductBundle\EventListener\Handler\HandlerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ProductEventHandlerPass
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEventHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('ekyna_product.registry.event_handler');
        foreach ($container->findTaggedServiceIds(HandlerInterface::DI_TAG) as $serviceId => $attributes) {
            $definition->addMethodCall('addHandler', [new Reference($serviceId)]);
        }
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\ProductBundle\EventListener\Handler\HandlerRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ProductEventHandlerPass
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEventHandlerPass implements CompilerPassInterface
{
    public const TAG = 'ekyna_product.product_event_handler';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(HandlerRegistry::class)) {
            throw new ServiceNotFoundException('Product event handlers registry is not available.');
        }

        $definition = $container->getDefinition(HandlerRegistry::class);
        foreach ($container->findTaggedServiceIds(self::TAG) as $serviceId => $attributes) {
            $definition->addMethodCall('addHandler', [new Reference($serviceId)]);
        }
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class RegisterProductEventHandlerPass
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegisterProductEventHandlerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ekyna_product.product.listener.handler_registry')) {
            throw new ServiceNotFoundException('Product event handlers registry is not available.');
        }

        $registryDefinition = $container->getDefinition('ekyna_product.product.listener.handler_registry');
        foreach ($container->findTaggedServiceIds('ekyna_product.product_event_handler') as $serviceId => $tag) {
            $registryDefinition->addMethodCall('addHandler', [new Reference($serviceId)]);
        }
    }
}

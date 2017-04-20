<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\ProductBundle\Attribute\Type\TypeInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AttributeTypeRegistryPass
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeTypeRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $registry = $container->getDefinition('ekyna_product.registry.attribute_type');

        foreach ($container->findTaggedServiceIds(TypeInterface::TYPE_TAG, true) as $serviceId => $tags) {
            $registry->addMethodCall('registerType', [new Reference($serviceId)]);
        }
    }
}

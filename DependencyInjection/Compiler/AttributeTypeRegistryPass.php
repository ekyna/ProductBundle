<?php

namespace Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AttributeTypeRegistryPass
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeTypeRegistryPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_product.attribute.type_registry')) {
            return;
        }

        $types = array();
        foreach ($container->findTaggedServiceIds('ekyna_product.attribute_type') as $serviceId => $tag) {
            if (!isset($tag[0]['alias'])) {
                throw new InvalidArgumentException(
                    "Attribute 'alias' is missing on tag 'ekyna_product.attribute_type' for service '$serviceId'."
                );
            }
            $types[$tag[0]['alias']] = new Reference($serviceId);
        }

        $container
            ->getDefinition('ekyna_product.attribute.type_registry')
            ->replaceArgument(0, $types);
    }
}
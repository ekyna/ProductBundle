<?php

namespace Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\ProductBundle\Service\Converter\ProductConverter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ConverterPass
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConverterPass implements CompilerPassInterface
{
    public const TAG = 'ekyna_product.converter';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(ProductConverter::class)) {
            throw new ServiceNotFoundException('Product converter is not available.');
        }

        $definition = $container->getDefinition(ProductConverter::class);

        foreach ($container->findTaggedServiceIds(self::TAG) as $serviceId => $attributes) {
            $definition->addMethodCall('registerConverter', [new Reference($serviceId)]);
        }
    }
}

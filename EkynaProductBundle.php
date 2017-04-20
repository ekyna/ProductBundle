<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle;

use Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class EkynaProductBundle
 * @package Ekyna\Bundle\ProductBundle
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaProductBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new Compiler\AdminMenuPass());
        $container->addCompilerPass(new Compiler\AttributeTypeRegistryPass());
        $container->addCompilerPass(new Compiler\ConverterPass());
        $container->addCompilerPass(new Compiler\ProductEventHandlerPass());
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler\AdminMenuPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * EkynaProductBundle
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaProductBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AdminMenuPass());
    }
}

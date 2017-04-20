<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Generator;

use DateTime;
use Ekyna\Component\Commerce\Common\Generator\AbstractGenerator;

/**
 * Class ReferenceGenerator
 * @package Ekyna\Bundle\ProductBundle\Service\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ReferenceGenerator extends AbstractGenerator
{
    protected function getPrefix(): string
    {
        return (new DateTime())->format($this->prefix);
    }
}

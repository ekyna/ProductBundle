<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class ComponentEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ComponentEvents
{
    public const INSERT = 'ekyna_product.component.insert';
    public const UPDATE = 'ekyna_product.component.update';
    public const DELETE = 'ekyna_product.component.delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

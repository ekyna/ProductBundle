<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class ComponentEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ComponentEvents
{
    const INSERT = 'ekyna_product.component.insert';
    const UPDATE = 'ekyna_product.component.update';
    const DELETE = 'ekyna_product.component.delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

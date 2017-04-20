<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class OptionEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OptionEvents
{
    public const INSERT = 'ekyna_product.option.insert';
    public const UPDATE = 'ekyna_product.option.update';
    public const DELETE = 'ekyna_product.option.delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

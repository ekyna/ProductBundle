<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class OptionGroupEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OptionGroupEvents
{
    public const INSERT = 'ekyna_product.option_group.insert';
    public const UPDATE = 'ekyna_product.option_group.update';
    public const DELETE = 'ekyna_product.option_group.delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

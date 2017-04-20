<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class BundleSlotEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class BundleSlotEvents
{
    public const INSERT = 'ekyna_product.bundle_slot.insert';
    public const UPDATE = 'ekyna_product.bundle_slot.update';
    public const DELETE = 'ekyna_product.bundle_slot.delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class BundleChoiceEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class BundleChoiceEvents
{
    public const INSERT = 'ekyna_product.bundle_choice.insert';
    public const UPDATE = 'ekyna_product.bundle_choice.update';
    public const DELETE = 'ekyna_product.bundle_choice.delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class BundleChoiceEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class BundleChoiceEvents
{
    const INSERT = 'ekyna_product.bundle_choice.insert';
    const UPDATE = 'ekyna_product.bundle_choice.update';
    const DELETE = 'ekyna_product.bundle_choice.delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

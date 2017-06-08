<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class OptionEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OptionEvents
{
    const INSERT = 'ekyna_product.option.insert';
    const UPDATE = 'ekyna_product.option.update';
    const DELETE = 'ekyna_product.option.delete';
}

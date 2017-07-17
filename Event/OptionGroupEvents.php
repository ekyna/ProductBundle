<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class OptionGroupEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OptionGroupEvents
{
    const INSERT = 'ekyna_product.option_group.insert';
    const UPDATE = 'ekyna_product.option_group.update';
    const DELETE = 'ekyna_product.option_group.delete';
}

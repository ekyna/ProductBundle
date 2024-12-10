<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

/**
 * Class Permission
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class Permission
{
    public const CHANGE_REFERENCE = 'change_reference';
    public const SYNC_REFERENCE   = 'sync_reference';

    private function __construct()
    {
    }
}

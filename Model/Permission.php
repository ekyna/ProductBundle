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
    public const CHANGE_REFERENCE            = 'change_reference';
    public const SYNC_REFERENCE              = 'sync_reference';
    public const GENERATE_EXTERNAL_REFERENCE = 'generate_external_reference';
    public const CHANGE_TYPE                 = 'change_type';
    public const VISIBILITY                  = 'visibility';
    public const STOCK_VIEW                  = 'stock_view';
    public const DASHBOARD_EXPORT            = 'dashboard_export';

    private function __construct()
    {
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\DateRange;

/**
 * Class SaleExportConfig
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleExportConfig
{
    public DateRange $range;
    public bool      $single = false;
    /**
     * Product references.
     * @var array<int, string>
     */
    public ?array $filter;
}

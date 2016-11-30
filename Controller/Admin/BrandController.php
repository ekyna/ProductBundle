<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Resource as RC;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;

/**
 * Class BrandController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class BrandController extends ResourceController
{
    use RC\SortableTrait,
        RC\TinymceTrait;
}

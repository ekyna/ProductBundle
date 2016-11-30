<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\AdminBundle\Controller\Resource as RC;

/**
 * Class CategoryController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryController extends ResourceController
{
    use RC\NestedTrait,
        RC\TinymceTrait;
}

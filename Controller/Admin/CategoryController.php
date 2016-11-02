<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\AdminBundle\Controller\Resource\NestedTrait;
use Ekyna\Bundle\AdminBundle\Controller\Resource\TinymceTrait;

/**
 * Class CategoryController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryController extends ResourceController
{
    use NestedTrait;
    use TinymceTrait;
}

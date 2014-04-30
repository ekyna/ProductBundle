<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\AdminBundle\Controller\Resource\NestedTrait;
use Ekyna\Bundle\AdminBundle\Controller\Resource\TinymceTrait;
use Ekyna\Bundle\CmsBundle\Controller\Resource\ContentTrait;

/**
 * CategoryController.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryController extends ResourceController
{
    use NestedTrait;
    use ContentTrait;
    use TinymceTrait;
}

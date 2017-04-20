<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class CategoryEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CategoryEvents
{
    public const INSERT      = 'ekyna_product.category.insert';
    public const UPDATE      = 'ekyna_product.category.update';
    public const DELETE      = 'ekyna_product.category.delete';

    public const PRE_CREATE  = 'ekyna_product.category.pre_create';
    public const POST_CREATE = 'ekyna_product.category.post_create';

    public const PRE_UPDATE  = 'ekyna_product.category.pre_update';
    public const POST_UPDATE = 'ekyna_product.category.post_update';

    public const PRE_DELETE  = 'ekyna_product.category.pre_delete';
    public const POST_DELETE = 'ekyna_product.category.post_delete';

    public const PUBLIC_URL  = 'ekyna_product.category.public_url';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

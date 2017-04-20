<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class BrandEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class BrandEvents
{
    public const INSERT      = 'ekyna_product.brand.insert';
    public const UPDATE      = 'ekyna_product.brand.update';
    public const DELETE      = 'ekyna_product.brand.delete';

    public const PRE_CREATE  = 'ekyna_product.brand.pre_create';
    public const POST_CREATE = 'ekyna_product.brand.post_create';

    public const PRE_UPDATE  = 'ekyna_product.brand.pre_update';
    public const POST_UPDATE = 'ekyna_product.brand.post_update';

    public const PRE_DELETE  = 'ekyna_product.brand.pre_delete';
    public const POST_DELETE = 'ekyna_product.brand.post_delete';

    public const PUBLIC_URL  = 'ekyna_product.brand.public_url';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class OfferEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OfferEvents
{
    public const INSERT      = 'ekyna_product.offer.insert';
    public const UPDATE      = 'ekyna_product.offer.update';
    public const DELETE      = 'ekyna_product.offer.delete';

    public const INITIALIZE  = 'ekyna_product.offer.initialize';

    public const PRE_CREATE  = 'ekyna_product.offer.pre_create';
    public const POST_CREATE = 'ekyna_product.offer.post_create';

    public const PRE_UPDATE  = 'ekyna_product.offer.pre_update';
    public const POST_UPDATE = 'ekyna_product.offer.post_update';

    public const PRE_DELETE  = 'ekyna_product.offer.pre_delete';
    public const POST_DELETE = 'ekyna_product.offer.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

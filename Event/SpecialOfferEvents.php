<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class SpecialOfferEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SpecialOfferEvents
{
    public const INSERT      = 'ekyna_product.special_offer.insert';
    public const UPDATE      = 'ekyna_product.special_offer.update';
    public const DELETE      = 'ekyna_product.special_offer.delete';

    public const PRE_CREATE  = 'ekyna_product.special_offer.pre_create';
    public const POST_CREATE = 'ekyna_product.special_offer.post_create';

    public const PRE_UPDATE  = 'ekyna_product.special_offer.pre_update';
    public const POST_UPDATE = 'ekyna_product.special_offer.post_update';

    public const PRE_DELETE  = 'ekyna_product.special_offer.pre_delete';
    public const POST_DELETE = 'ekyna_product.special_offer.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class OfferEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OfferEvents
{
    const INSERT      = 'ekyna_product.offer.insert';
    const UPDATE      = 'ekyna_product.offer.update';
    const DELETE      = 'ekyna_product.offer.delete';

    const INITIALIZE  = 'ekyna_product.offer.initialize';

    const PRE_CREATE  = 'ekyna_product.offer.pre_create';
    const POST_CREATE = 'ekyna_product.offer.post_create';

    const PRE_UPDATE  = 'ekyna_product.offer.pre_update';
    const POST_UPDATE = 'ekyna_product.offer.post_update';

    const PRE_DELETE  = 'ekyna_product.offer.pre_delete';
    const POST_DELETE = 'ekyna_product.offer.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class SpecialOfferEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SpecialOfferEvents
{
    const INSERT      = 'ekyna_product.special_offer.insert';
    const UPDATE      = 'ekyna_product.special_offer.update';
    const DELETE      = 'ekyna_product.special_offer.delete';

    const INITIALIZE  = 'ekyna_product.special_offer.initialize';

    const PRE_CREATE  = 'ekyna_product.special_offer.pre_create';
    const POST_CREATE = 'ekyna_product.special_offer.post_create';

    const PRE_UPDATE  = 'ekyna_product.special_offer.pre_update';
    const POST_UPDATE = 'ekyna_product.special_offer.post_update';

    const PRE_DELETE  = 'ekyna_product.special_offer.pre_delete';
    const POST_DELETE = 'ekyna_product.special_offer.post_delete';
}

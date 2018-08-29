<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class SpecialOffer
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SpecialOffer extends Constraint
{
    public $at_least_one_brand_or_product = 'ekyna_product.special_offer.at_least_one_brand_or_product';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

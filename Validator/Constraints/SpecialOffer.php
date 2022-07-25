<?php /** @noinspection PhpPropertyNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class SpecialOffer
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SpecialOffer extends Constraint
{
    public string $brands_must_be_empty                           = 'ekyna_product.pricing.brands_must_be_empty';
    public string $pricing_groups_must_be_empty                   = 'ekyna_product.pricing.pricing_groups_must_be_empty';
    public string $products_must_be_empty                         = 'ekyna_product.special_offer.pricing_groups_must_be_empty';
    public string $at_least_one_pricing_group_or_brand_or_product = 'ekyna_product.special_offer.at_least_one_pricing_group_or_brand_or_product';

    public function getTargets(): string
    {
        return static::CLASS_CONSTRAINT;
    }
}

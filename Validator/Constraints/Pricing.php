<?php /** @noinspection PhpPropertyNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Pricing
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Pricing extends Constraint
{
    public string $brands_must_be_empty                = 'ekyna_product.pricing.brands_must_be_empty';
    public string $pricing_groups_must_be_empty        = 'ekyna_product.pricing.pricing_groups_must_be_empty';
    public string $at_least_one_pricing_group_or_brand = 'ekyna_product.pricing.at_least_one_pricing_group_or_brand';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\PricingGroupInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Class PricingGroupRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<PricingGroupInterface>
 */
class PricingGroupRepository extends ResourceRepository implements PricingGroupRepositoryInterface
{
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\TranslatableRepository;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Class AttributeRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<AttributeInterface>
 */
class AttributeRepository extends TranslatableRepository
{
    protected function getAlias(): string
    {
        return 'a';
    }
}

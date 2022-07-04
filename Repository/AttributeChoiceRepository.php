<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\AttributeChoiceInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\TranslatableRepository;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Class AttributeChoiceRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<AttributeChoiceInterface>
 */
class AttributeChoiceRepository extends TranslatableRepository
{
    protected function getAlias(): string
    {
        return 'ac';
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepository;

/**
 * Class AttributeGroupRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeGroupRepository extends TranslatableResourceRepository
{
    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'ag';
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepository;

/**
 * Class OptionGroupRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroupRepository extends TranslatableResourceRepository
{
    /**
     * @inheritDoc
     */
    protected function getAlias()
    {
        return 'og';
    }
}

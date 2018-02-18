<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepository;

/**
 * Class AttributeChoiceRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeChoiceRepository extends TranslatableResourceRepository
{
    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'ac';
    }
}

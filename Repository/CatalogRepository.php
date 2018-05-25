<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class CatalogRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogRepository extends ResourceRepository
{
    /**
     * @inheritDoc
     */
    protected function getAlias()
    {
        return 'c';
    }
}

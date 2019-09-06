<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductReferenceInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface ProductReferenceRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductReferenceRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds one product reference by type and code.
     *
     * @param string $type The reference type
     * @param string $code The reference code
     *
     * @return ProductReferenceInterface|null
     */
    public function findOneByTypeAndCode(string $type, string $code): ?ProductReferenceInterface;
}

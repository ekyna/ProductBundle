<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductReferenceInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface ProductReferenceRepositoryInterface
 * @package      Ekyna\Bundle\ProductBundle\Repository
 * @author       Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductReferenceRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds one product reference by type and code.
     */
    public function findOneByTypeAndCode(string $type, string $code): ?ProductReferenceInterface;
}

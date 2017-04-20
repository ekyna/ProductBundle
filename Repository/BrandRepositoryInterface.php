<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;

/**
 * Interface BrandRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method BrandInterface find($id)
 */
interface BrandRepositoryInterface extends TranslatableRepositoryInterface
{
    public function findOneBySlug(string $slug): ?BrandInterface;
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\CategoryInterface;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;

/**
 * Interface CategoryRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface CategoryRepositoryInterface extends TranslatableRepositoryInterface
{
    public function findOneBySlug(string $slug): ?CategoryInterface;

    /**
     * @return array<CategoryInterface>
     */
    public function findForMenu(): array;
}

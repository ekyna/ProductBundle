<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\ProductBundle\Entity\ProductBookmark;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Class ProductBookmarkRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductBookmarkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductBookmark::class);
    }

    /**
     * Finds the bookmark by user and product.
     */
    public function findBookmark(UserInterface $user, ProductInterface $product): ?ProductBookmark
    {
        return $this->findOneBy([
            'user' => $user,
            'product' => $product,
        ]);
    }
}

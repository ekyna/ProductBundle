<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\ProductBundle\Entity\ProductBookmark;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Class ProductBookmarkRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductBookmarkRepository extends EntityRepository
{
    /**
     * Finds the book mark by user and product.
     *
     * @param UserInterface    $user
     * @param ProductInterface $product
     *
     * @return ProductBookmark|null
     */
    public function findBookmark(UserInterface $user, ProductInterface $product): ?ProductBookmark
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findOneBy([
            'user' => $user,
            'product' => $product,
        ]);
    }

    /**
     * Returns the user's bookmarked products identifiers.
     *
     * @param UserInterface $user
     *
     * @return array
     *
     * @TODO Remove as not used
     */
    public function getBookmarkedIds(UserInterface $user): array
    {
        $qb = $this->createQueryBuilder('b');

        $result = $qb
            ->select('IDENTITY(b.product)')
            ->where($qb->expr()->eq('b.user', $user))
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'id');
    }
}

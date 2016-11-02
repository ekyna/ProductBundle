<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepository;

/**
 * Class ProductRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductRepository extends TranslatableResourceRepository implements ProductRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findOneById($id)
    {
        return $this->find($id);
    }

    /**
     * @inheritdoc
     */
    public function findParentsByBundled(ProductInterface $bundled)
    {
        $qb = $this->getQueryBuilder();

        return $qb
            ->join('o.bundleSlots', 'slot')
            ->join('slot.choices', 'choice')
            ->andWhere($qb->expr()->eq('choice.product', ':bundled'))
            ->setParameter('bundled', $bundled)
            ->getQuery()
            ->getResult();
    }
}

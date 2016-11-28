<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model;
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
    public function findParentsByBundled(Model\ProductInterface $bundled)
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

    /**
     * @inheritdoc
     */
    public function findByCategory(Model\CategoryInterface $category, $recursive = false)
    {
        $qb = $this->getCollectionQueryBuilder();

        $query = $qb
            ->andWhere($qb->expr()->isMemberOf(':categories', 'o.categories'))
            ->andWhere($qb->expr()->in('o.type', ':types'))
            ->getQuery();

        $categories = [$category];
        if ($recursive) {
            $categories = array_merge($categories, $category->getChildren()->toArray());
        };

        return $query
            ->setParameters([
                'categories' => $categories,
                'types' => [
                    Model\ProductTypes::TYPE_SIMPLE,
                    Model\ProductTypes::TYPE_VARIABLE,
                    Model\ProductTypes::TYPE_BUNDLE,
                    Model\ProductTypes::TYPE_CONFIGURABLE,
                ]
            ])
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findByBrand(Model\BrandInterface $brand)
    {
        $qb = $this->getCollectionQueryBuilder();

        $query = $qb
            ->andWhere($qb->expr()->eq('o.brand', ':brand'))
            ->andWhere($qb->expr()->in('o.type', ':types'))
            ->getQuery();

        return $query
            ->setParameters([
                'brand' => $brand,
                'types' => [
                    Model\ProductTypes::TYPE_SIMPLE,
                    Model\ProductTypes::TYPE_VARIABLE,
                    Model\ProductTypes::TYPE_BUNDLE,
                    Model\ProductTypes::TYPE_CONFIGURABLE,
                ]
            ])
            ->getResult();
    }
}

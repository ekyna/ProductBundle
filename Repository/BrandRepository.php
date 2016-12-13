<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Entity\Brand;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepository;

/**
 * Class BrandRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BrandRepository extends TranslatableResourceRepository
{
    /**
     * Finds the brand by slug.
     *
     * @param string $slug
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\BrandInterface|null
     */
    public function findOneBySlug($slug)
    {
        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq('translation.slug', ':slug'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, 3600, Brand::getEntityTagPrefix() . '[slug=' . $slug . ']')
            ->setParameters([
                'slug' => $slug
            ])
            ->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'c';
    }
}

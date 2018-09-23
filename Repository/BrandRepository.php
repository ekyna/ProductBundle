<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\ORM\Query\Expr;
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
        $as = $this->getAlias();

        return $qb
            ->leftJoin($as . '.seo', 's')
            ->leftJoin('b.translations', 'b_t', Expr\Join::WITH, $this->getLocaleCondition('b_t'))
            ->addSelect('b', 'b_t', 's')
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq('b_t.slug', ':slug'))
            ->andWhere($qb->expr()->eq('b_t.locale', ':locale'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->useResultCache(true, 3600, Brand::getEntityTagPrefix() . '[slug=' . $slug . ']')
            ->setParameters([
                'visible' => true,
                'slug'    => $slug,
                'locale'  => $this->localeProvider->getCurrentLocale(),
            ])
            ->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'b';
    }
}

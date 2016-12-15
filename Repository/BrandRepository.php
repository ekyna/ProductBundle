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

        return $qb
            ->leftJoin($this->getAlias() . '.seo', 's')
            ->leftJoin('s.translations', 's_t', Expr\Join::WITH, $this->getLocaleCondition('s_t'))
            ->addSelect('s', 's_t')
            ->andWhere($qb->expr()->eq('translation.slug', ':slug'))
            ->andWhere($qb->expr()->eq('translation.locale', ':locale'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->useResultCache(true, 3600, Brand::getEntityTagPrefix() . '[slug=' . $slug . ']')
            ->setParameters([
                'slug'   => $slug,
                'locale' => $this->localeProvider->getCurrentLocale(),
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

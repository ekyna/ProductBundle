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
        $as = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->resetDQLPart('join')
            ->resetDQLPart('select')
            ->leftJoin($as . '.translations', 't', Expr\Join::WITH, $this->getLocaleCondition('t'))
            ->leftJoin($as . '.seo', 's')
            ->leftJoin('s.translations', 's_t', Expr\Join::WITH, $this->getLocaleCondition('s_t'))
            ->addSelect($as, 't', 's', 's_t')
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq('t.slug', ':slug'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->enableResultCache(3600, Brand::getEntityTagPrefix() . '[slug=' . $slug . ']')
            ->setParameters([
                'visible' => true,
                'slug'    => $slug,
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

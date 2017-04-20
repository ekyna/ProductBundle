<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\ORM\Query\Expr;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\TranslatableRepository;

/**
 * Class BrandRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BrandRepository extends TranslatableRepository implements BrandRepositoryInterface
{
    public function findOneBySlug(string $slug): ?BrandInterface
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
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->enableResultCache(3600, Brand::getEntityTagPrefix() . '[slug=' . $slug . ']')
            ->setParameters([
                'visible' => true,
                'slug'    => $slug,
            ])
            ->getOneOrNullResult();
    }

    protected function getAlias(): string
    {
        return 'b';
    }
}

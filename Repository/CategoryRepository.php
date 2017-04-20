<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\ORM\Query\Expr;
use Ekyna\Bundle\ProductBundle\Model\CategoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\TranslatableRepository;

/**
 * Class CategoryRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryRepository extends TranslatableRepository implements CategoryRepositoryInterface
{
    public function findOneBySlug(string $slug): ?CategoryInterface
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
            // TODO ->enableResultCache(3600, $this->getCachePrefix() . '[slug=' . $slug . ']')
            ->setParameters([
                'visible' => true,
                'slug'    => $slug,
            ])
            ->getOneOrNullResult();
    }

    public function findForMenu(): array
    {
        $as = $this->getAlias();
        $qb = $this->getCollectionQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq($as . '.level', ':level'))
            ->addOrderBy($as . '.left', 'ASC')
            ->addOrderBy($as . '.id', 'ASC')
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->enableResultCache(3600, $this->getCachePrefix() . '.menu')
            ->setParameters([
                'visible' => true,
                'level'   => 0,
            ])
            ->getResult();
    }

    protected function getAlias(): string
    {
        return 'c';
    }
}

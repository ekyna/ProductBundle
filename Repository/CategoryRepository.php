<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\ORM\Query\Expr;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Util\TranslatableResourceRepositoryTrait;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Class CategoryRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryRepository extends NestedTreeRepository implements TranslatableResourceRepositoryInterface
{
    use TranslatableResourceRepositoryTrait;


    /**
     * Finds the category by slug.
     *
     * @param string $slug
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\CategoryInterface|null
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
            // TODO ->useResultCache(true, 3600, $this->getCachePrefix() . '[slug=' . $slug . ']')
            ->setParameters([
                'visible' => true,
                'slug'    => $slug,
            ])
            ->getOneOrNullResult();
    }

    /**
     * Finds the categories for the navbar menu.
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\CategoryInterface[]
     */
    public function findForMenu()
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
            // TODO ->useResultCache(true, 3600, $this->getCachePrefix() . '.menu')
            ->setParameters([
                'visible' => true,
                'level'   => 0,
            ])
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'c';
    }
}

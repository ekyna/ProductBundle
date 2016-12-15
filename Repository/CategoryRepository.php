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
        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->leftJoin($alias.'.seo', 's')
            ->leftJoin('s.translations', 's_t', Expr\Join::WITH, $this->getLocaleCondition('s_t'))
            ->addSelect('s', 's_t')
            ->andWhere($qb->expr()->eq('translation.slug', ':slug'))
            ->andWhere($this->getLocaleCondition())
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->useResultCache(true, 3600, $this->getCachePrefix() . '[slug=' . $slug . ']')
            ->setParameters([
                'slug' => $slug,
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
        $alias = $this->getAlias();
        $qb = $this->createQueryBuilder();

        return $qb
            ->leftJoin($alias . '.translations', 'c_t', Expr\Join::WITH, $this->getLocaleCondition('c_t'))
            ->addSelect('c_t')
            ->andWhere($qb->expr()->eq($alias . '.level', ':level'))
            ->addOrderBy($alias . '.left', 'ASC')
            ->addOrderBy($alias . '.id', 'ASC')
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, 3600, $this->getCachePrefix())
            ->setParameters([
                'level' => 0,
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

<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
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
    public function findOneBySlug($slug)
    {
        $qb = $this->getQueryBuilder();

        $this
            ->joinBrand($qb)
            ->joinSeo($qb);

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
        $product = $qb
            ->andWhere($qb->expr()->eq('translation.slug', ':slug'))
            ->andWhere($qb->expr()->eq('translation.locale', ':locale'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->useResultCache(true, 3600, $this->getCachePrefix() . '[slug=' . $slug . ']')
            ->setParameters([
                'slug'   => $slug,
                'locale' => $this->localeProvider->getCurrentLocale(),
            ])
            ->getOneOrNullResult();

        $this->loadAssociations($product);

        return $product;
    }

    /**
     * @inheritdoc
     */
    public function findByBrand(Model\BrandInterface $brand)
    {
        $as = $this->getAlias();
        $qb = $this->getCollectionQueryBuilder();

        $this->joinBrand($qb);

        $query = $qb
            ->andWhere($qb->expr()->eq($as . '.brand', ':brand'))
            ->andWhere($qb->expr()->in($as . '.type', ':types'))
            ->getQuery()
            ->useQueryCache(true);

        return $query
            ->setParameters([
                'brand' => $brand,
                'types' => [
                    Model\ProductTypes::TYPE_SIMPLE,
                    Model\ProductTypes::TYPE_VARIABLE,
                    Model\ProductTypes::TYPE_BUNDLE,
                    Model\ProductTypes::TYPE_CONFIGURABLE,
                ],
            ])
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findByCategory(Model\CategoryInterface $category, $recursive = false)
    {
        $as = $this->getAlias();
        $qb = $this->getCollectionQueryBuilder();

        $this->joinBrand($qb);

        $query = $qb
            ->andWhere($qb->expr()->isMemberOf(':categories', $as . '.categories'))
            ->andWhere($qb->expr()->in($as . '.type', ':types'))
            ->getQuery()
            ->useQueryCache(true);

        $categories = [$category];
        if ($recursive) {
            $categories = array_merge($categories, $category->getChildren()->toArray());
        };

        return $query
            ->setParameters([
                'categories' => $categories,
                'types'      => [
                    Model\ProductTypes::TYPE_SIMPLE,
                    Model\ProductTypes::TYPE_VARIABLE,
                    Model\ProductTypes::TYPE_BUNDLE,
                    Model\ProductTypes::TYPE_CONFIGURABLE,
                ],
            ])
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findParentsByBundled(Model\ProductInterface $bundled)
    {
        $as = $this->getAlias();
        $qb = $this->getQueryBuilder(); // TODO do we need translation join ?

        return $qb
            ->leftJoin($as . '.bundleSlots', 'slot')
            ->leftJoin('slot.choices', 'choice')
            ->andWhere($qb->expr()->eq('choice.product', ':bundled'))
            ->setParameter('bundled', $bundled)
            ->getQuery()
            ->useQueryCache(true)
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function loadMedias(Model\ProductInterface $product)
    {
        if (!$this->isInitializedCollection($product->getMedias())) {
            $qb = $this->createQueryBuilder('p');
            $qb
                ->leftJoin('p.medias', 'pm')
                ->leftJoin('pm.media', 'm')
                ->leftJoin('m.translations', 'm_t', Expr\Join::WITH, $this->getLocaleCondition('m_t'))
                ->select('PARTIAL p.{id}', 'pm', 'm', 'm_t')
                ->andWhere($qb->expr()->eq('p.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameters([
                    'id' => $product->getId(),
                ])
                ->getResult();
        }
    }

    /**
     * @inheritdoc
     */
    public function loadOptions(Model\ProductInterface $product)
    {
        if (!$this->isInitializedCollection($product->getOptionGroups())) {
            $qb = $this->createQueryBuilder('p');
            $qb
                ->leftJoin('p.optionGroups', 'og')
                ->leftJoin('og.translations', 'og_t', Expr\Join::WITH, $this->getLocaleCondition('og_t'))
                ->leftJoin('og.options', 'o')
                ->leftJoin('o.translations', 'o_t', Expr\Join::WITH, $this->getLocaleCondition('o_t'))
                ->select('PARTIAL p.{id}', 'og', 'og_t', 'o', 'o_t')
                ->andWhere($qb->expr()->eq('p.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameters([
                    'id' => $product->getId(),
                ])
                ->getResult();
        }
    }

    /**
     * @inheritdoc
     */
    public function loadVariants(Model\ProductInterface $variable)
    {
        Model\ProductTypes::assertVariable($variable);

        if (!$this->isInitializedCollection($variable->getVariants())) {
            $qb = $this->createQueryBuilder('p');
            $qb
                ->leftJoin('p.variants', 'v')
                ->leftJoin('v.translations', 'v_t', Expr\Join::WITH, $this->getLocaleCondition('v_t'))
                ->select('PARTIAL p.{id}', 'v', 'v_t')
                ->andWhere($qb->expr()->eq('p.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameters([
                    'id' => $variable->getId(),
                ])
                ->getResult();
        }
    }

    /**
     * @inheritdoc
     */
    public function loadBundleSlots(Model\ProductInterface $bundle)
    {
        Model\ProductTypes::assertBundle($bundle);

        if (!$this->isInitializedCollection($bundle->getBundleSlots())) {
            $qb = $this->createQueryBuilder('p');
            $qb
                // Slots
                ->leftJoin('p.bundleSlots', 'bs')
                ->leftJoin('bs.translations', 'bs_t', Expr\Join::WITH, $this->getLocaleCondition('bs_t'))
                // Choices
                ->leftJoin('bs.choices', 'bc')
                // Choices products
                ->leftJoin('bc.product', 'bcp')
                ->leftJoin('bcp.translations', 'bcp_t', Expr\Join::WITH, $this->getLocaleCondition('bcp_t'))
                // Choices products brands
                ->leftJoin('bcp.brand', 'bcb')
                ->leftJoin('bcb.translations', 'bcb_t', Expr\Join::WITH, $this->getLocaleCondition('bcb_t'))
                ->select('PARTIAL p.{id}', 'bs', 'bs_t', 'bc', 'bcp', 'bcp_t', 'bcb', 'bcb_t')
                ->andWhere($qb->expr()->eq('p.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameters([
                    'id' => $bundle->getId(),
                ])
                ->getResult();
        }
    }

    /**
     * @inheritdoc
     */
    public function loadConfigurableSlots(Model\ProductInterface $configurable)
    {
        Model\ProductTypes::assertConfigurable($configurable);

        if (!$this->isInitializedCollection($configurable->getBundleSlots())) {
            // Slots
            $qb = $this->createQueryBuilder('p');
            $qb
                ->leftJoin('p.bundleSlots', 'bs')
                ->leftJoin('bs.translations', 'bs_t', Expr\Join::WITH, $this->getLocaleCondition('bs_t'))
                ->select('PARTIAL p.{id}', 'bs', 'bs_t')
                ->andWhere($qb->expr()->eq('p.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameters([
                    'id' => $configurable->getId(),
                ])
                ->getResult();

            // Slot choices
            $qb = $this->createQueryBuilder('p');
            $qb
                ->leftJoin('p.bundleSlots', 'bs')
                ->leftJoin('bs.choices', 'bc')
                ->leftJoin('bc.product', 'bcp')
                ->leftJoin('bcp.translations', 'bcp_t', Expr\Join::WITH, $this->getLocaleCondition('bcp_t'))
                ->leftJoin('bcp.brand', 'bcb')
                ->leftJoin('bcb.translations', 'bcb_t', Expr\Join::WITH, $this->getLocaleCondition('bcb_t'))
                ->select('PARTIAL p.{id}', 'PARTIAL bs.{id}', 'bc', 'bcp', 'bcp_t', 'bcb', 'bcb_t')
                ->andWhere($qb->expr()->eq('p.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameters([
                    'id' => $configurable->getId(),
                ])
                ->getResult();
        }
    }

    /**
     * Loads the product associations.
     *
     * @param Model\ProductInterface|null $product
     */
    protected function loadAssociations(Model\ProductInterface $product = null)
    {

        if (null !== $product) {
            // Medias
            $this->loadMedias($product);

            if ($product->getType() === Model\ProductTypes::TYPE_VARIABLE) {
                // Variants
                $this->loadVariants($product);
            } elseif ($product->getType() === Model\ProductTypes::TYPE_BUNDLE) {
                // Bundle slots
                $this->loadBundleSlots($product);
            } elseif ($product->getType() === Model\ProductTypes::TYPE_CONFIGURABLE) {
                // Configurable slots
                $this->loadConfigurableSlots($product);
            }
        }
    }

    /**
     * Adds the join parts for brand to the query builder.
     *
     * @param QueryBuilder $qb
     * @param string       $alias
     *
     * @return ProductRepository
     */
    protected function joinBrand(QueryBuilder $qb, $alias = null)
    {
        $alias = $alias ?: $this->getAlias();

        $qb
            ->join($alias . '.brand', 'b')
            ->leftJoin('b.translations', 'b_t', Expr\Join::WITH, $this->getLocaleCondition('b_t'))
            ->addSelect('b', 'b_t');

        return $this;
    }

    /**
     * Adds the join parts for seo to the query builder.
     *
     * @param QueryBuilder $qb
     * @param string       $alias
     *
     * @return ProductRepository
     */
    protected function joinSeo(QueryBuilder $qb, $alias = null)
    {
        $alias = $alias ?: $this->getAlias();

        $qb
            ->leftJoin($alias . '.seo', 's')
            ->leftJoin('s.translations', 's_t', Expr\Join::WITH, $this->getLocaleCondition('s_t'))
            ->addSelect('s', 's_t');

        return $this;
    }

    /**
     * Returns whether the collection has been initialized or not.
     *
     * @param Collection $collection
     *
     * @return bool
     */
    protected function isInitializedCollection(Collection $collection = null)
    {
        return (null !== $collection)
            && method_exists($collection, 'isInitialized')
            && $collection->{'isInitialized'}();
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'p';
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Ekyna\Bundle\ProductBundle\Model\ProductTranslationInterface;

use function is_null;

/**
 * Class ProductTranslationRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTranslationRepository extends ServiceEntityRepository implements ProductTranslationRepositoryInterface
{
    public function findDuplicate(ProductTranslationInterface $translation): ?ProductTranslationInterface
    {
        if (empty($title = $translation->getTitle())) {
            return null;
        }

        if (empty($locale = $translation->getLocale())) {
            return null;
        }

        if (is_null($product = $translation->getTranslatable())) {
            return null;
        }

        if (is_null($brand = $product->getBrand())) {
            return null;
        }

        $qb = $this->createQueryBuilder('t');
        $qb
            ->join('t.translatable', 'p')
            ->andWhere($qb->expr()->isNotNull('t.title'))
            ->andWhere($qb->expr()->eq('t.title', ':title'))
            ->andWhere($qb->expr()->eq('t.locale', ':locale'))
            ->andWhere($qb->expr()->eq('p.brand', ':brand'))
            ->setParameter('title', $title)
            ->setParameter('locale', $locale)
            ->setParameter('brand', $brand);

        if ($id = $translation->getId()) {
            $qb
                ->andWhere($qb->expr()->neq('t.id', ':id'))
                ->setParameter('id', $id);
        }

        return $qb
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->getOneOrNullResult();
    }
}

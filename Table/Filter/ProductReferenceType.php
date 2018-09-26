<?php

namespace Ekyna\Bundle\ProductBundle\Table\Filter;

use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntityAdapter;
use Ekyna\Component\Table\Context\ActiveFilter;
use Ekyna\Component\Table\Extension\Core\Type\Filter\TextType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Ekyna\Component\Table\Filter\FilterInterface;
use Ekyna\Component\Table\Source\AdapterInterface;

/**
 * Class ProductReferenceType
 * @package Ekyna\Bundle\ProductBundle\Table\Filter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductReferenceType extends AbstractFilterType
{
    /**
     * @inheritdoc
     */
    public function applyFilter(AdapterInterface $adapter, FilterInterface $filter, ActiveFilter $activeFilter, array $options)
    {
        if (!$adapter instanceof EntityAdapter) {
            return false;
        }

        $qb = $adapter->getQueryBuilder();
        $alias = $qb->getRootAliases()[0];

        $vReferencePath = $adapter->getQueryBuilderPath('variants.reference');

        $qb
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq($alias . '.reference', ':reference'),
                $qb->expr()->eq($vReferencePath, ':reference')
            ))
            ->setParameter('reference', $activeFilter->getValue());

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return TextType::class;
    }
}

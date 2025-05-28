<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stock;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Entity\Product;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierProduct;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;

use function sprintf;

/**
 * Class StockRepository
 * @package Ekyna\Bundle\ProductBundle\Service\Stock
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockRepository
{
    private const PENDING_DQL = "(
  SELECT SUM(nsoi.quantity * nsoi.packing) 
  FROM _class_ nsoi
  JOIN nsoi.product nsp
  JOIN nsoi.order nso
  WHERE nsp.subjectIdentity.provider = :provider
    AND nsp.subjectIdentity.identifier = p.id
    AND (nso.state = '_state_new_' OR nso.state = '_state_ordered_')
) AS pending";

    private const STOCK_SUB_DQL = "(
    SELECT SUM(_table_._field_)
    FROM _class_ _table_
    WHERE _table_.state <> '_state_'
    AND _table_.product = p.id
) AS _alias_";

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        FormatterFactory                        $formatterFactory,
        private readonly string                 $productClass,
        private readonly string                 $stockUnitClass,
        private readonly string                 $supplierOrderItemClass,
        private readonly string                 $supplierProductClass,
    ) {
    }

    public function getProductsQueryBuilder(): QueryBuilder
    {
        /**
         * @TODO Do not add fields twice
         * @see \Ekyna\Bundle\ProductBundle\Service\Stock\StockView::getProductsQueryBuilder
         */
        $pQb = $this->entityManager->createQueryBuilder();
        $pQb
            ->from($this->productClass/** @type Product */, 'p')
            ->select([
                'p.id',
                'p.type',
                'b.name as brand',
                'p.reference',
                'p.designation',
                'p.netPrice as net_price',
                'p.attributesDesignation as attributes_designation',
                'p.endOfLife as end_of_life',
                'p.replenishmentTime as replenishment',
                'p.inStock as in_stock',
                'p.availableStock as available_stock',
                'p.virtualStock as virtual_stock',
                'p.stockFloor as stock_floor',
                'p.estimatedDateOfArrival as eda',
                'parent.designation as parent_designation',
            ])
            ->addSelect($this->getPendingSubQuery())
            ->addSelect($this->buildStockSubQuery('orderedQuantity', 'ordered', 'su1'))
            ->addSelect($this->buildStockSubQuery('receivedQuantity', 'received', 'su2'))
            ->addSelect($this->buildStockSubQuery('adjustedQuantity', 'adjusted', 'su3'))
            ->addSelect($this->buildStockSubQuery('soldQuantity', 'sold', 'su4'))
            ->addSelect($this->buildStockSubQuery('shippedQuantity', 'shipped', 'su5'))
            ->leftJoin('p.brand', 'b')
            ->leftJoin('p.parent', 'parent')
            ->andWhere($pQb->expr()->in('p.type', ':types'))
            ->setParameters([
                'types'    => [ProductTypes::TYPE_SIMPLE, ProductTypes::TYPE_VARIANT],
                'provider' => ProductProvider::getName(),
            ]);

        return $pQb;
    }

    /**
     * Normalizes the product.
     *
     * @param array $product The database product data
     *
     * @return array The normalized product data
     */
    public function normalizeProduct(array &$product): array
    {
        $product['id'] = (int)$product['id'];

        // Designation
        if ($product['type'] === ProductTypes::TYPE_VARIANT) {
            $product['designation'] = sprintf(
                '%s %s %s',
                $product['brand'],
                $product['parent_designation'],
                $product['designation'] ?? $product['attributes_designation']
            );
        } else {
            $product['designation'] = sprintf(
                '%s %s',
                $product['brand'],
                $product['designation']
            );
        }

        return $product;
    }

    /**
     * Builds the supplier sub query.
     *
     * @return string
     */
    public function buildSupplierSubQuery(): string
    {
        $sQb = $this->entityManager->createQueryBuilder();
        $sQb
            ->from($this->supplierProductClass/** @type SupplierProduct */, 'sp')
            ->select('sp.subjectIdentity.identifier')
            ->andWhere($sQb->expr()->eq('sp.subjectIdentity.identifier', 'p.id'))
            ->andWhere($sQb->expr()->eq('sp.supplier', ':supplier'));

        return $sQb->getDQL();
    }

    /**
     * Builds the pending stock sub query.
     *
     * i.e. Ordered quantity of 'new' supplier orders.
     *
     * @return string
     */
    private function getPendingSubQuery(): string
    {
        return strtr(static::PENDING_DQL, [
            '_class_'         => $this->supplierOrderItemClass,
            '_state_new_'     => SupplierOrderStates::STATE_NEW,
            '_state_ordered_' => SupplierOrderStates::STATE_ORDERED,
        ]);
    }

    /**
     * Builds the stock sub query.
     *
     * @param string $field
     * @param string $fieldAlias
     * @param string $tableAlias
     *
     * @return string
     */
    private function buildStockSubQuery(string $field, string $fieldAlias, string $tableAlias): string
    {
        return strtr(static::STOCK_SUB_DQL, [
            '_field_' => $field,
            '_class_' => $this->stockUnitClass,
            '_table_' => $tableAlias,
            '_state_' => StockUnitStates::STATE_CLOSED,
            '_alias_' => $fieldAlias,
        ]);
    }
}

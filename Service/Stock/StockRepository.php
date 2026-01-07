<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stock;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Entity\Product;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Manufacture\Model\BOMState;
use Ekyna\Component\Commerce\Manufacture\Model\POState;
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
    private const PENDING_SUPPLY_DQL = "(
    SELECT SUM(nsoi.quantity * nsoi.packing) 
    FROM _class_ nsoi
    JOIN nsoi.product nsp
    JOIN nsoi.order nso
    WHERE nsp.subjectIdentity.provider = :provider
      AND nsp.subjectIdentity.identifier = p.id
      AND (nso.state = '_state_new_' OR nso.state = '_state_ordered_')
) AS pending_supply";

    private const SUPPLIER_PRODUCT_DQL = "(
    SELECT COUNT(hsp.id)
    FROM _class_ hsp
    WHERE hsp.subjectIdentity.provider = :provider
      AND hsp.subjectIdentity.identifier = p.id
) AS has_supplier_product";

    private const PENDING_PRODUCTION_DQL = "(
    SELECT SUM(npo.quantity) 
    FROM _class_ npo
    WHERE npo.subjectIdentity.provider = :provider
      AND npo.subjectIdentity.identifier = p.id
      AND npo.state = '_state_new_'
) AS pending_production";

    private const BOM_DQL = "(
    SELECT COUNT(hb.id)
    FROM _class_ hb
    WHERE hb.subjectIdentity.provider = :provider
      AND hb.subjectIdentity.identifier = p.id
      AND hb.state = '_state_validated_'
) AS has_bom";

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
        private readonly string $productionOrderClass,
        private readonly string $billOfMaterialClass,
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
            ->addSelect($this->getPendingSupplySubQuery())
            ->addSelect($this->getSupplierProductSubQuery())
            ->addSelect($this->getPendingProductionSubQuery())
            ->addSelect($this->getBOMSubQuery())
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
     * Builds the pending supply sub query.
     *
     * i.e. Quantity of 'new' supplier orders.
     *
     * @return string
     */
    private function getPendingSupplySubQuery(): string
    {
        return strtr(static::PENDING_SUPPLY_DQL, [
            '_class_'         => $this->supplierOrderItemClass,
            '_state_new_'     => SupplierOrderStates::STATE_NEW,
            '_state_ordered_' => SupplierOrderStates::STATE_ORDERED,
        ]);
    }

    /**
     * Builds the 'has supplier product' stock sub query.
     *
     * @return string
     */
    private function getSupplierProductSubQuery(): string
    {
        return strtr(static::SUPPLIER_PRODUCT_DQL, [
            '_class_' => $this->supplierProductClass,
        ]);
    }

    /**
     * Builds the pending production sub query.
     *
     * i.e. Quantity of 'new' production orders.
     *
     * @return string
     */
    private function getPendingProductionSubQuery(): string
    {
        return strtr(static::PENDING_PRODUCTION_DQL, [
            '_class_'     => $this->productionOrderClass,
            '_state_new_' => POState::NEW->value,
        ]);
    }

    /**
     * Builds the 'has validated bom' stock sub query.
     *
     * @return string
     */
    private function getBOMSubQuery(): string
    {
        return strtr(static::BOM_DQL, [
            '_class_'           => $this->billOfMaterialClass,
            '_state_validated_' => BOMState::VALIDATED->value,
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

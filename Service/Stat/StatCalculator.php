<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use DateTime;
use Doctrine\DBAL\Connection;
use Ekyna\Bundle\ProductBundle\Entity\StatCount;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface as Product;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface as Group;

use function implode;

/**
 * Class StatCalculator
 * @package Ekyna\Bundle\ProductBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    Use ProductProvider::NAME instead of 'product' in queries
 */
class StatCalculator
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * Calculates the product sold quantity for the given date range.
     */
    public function calculateCount(Product $product, string $source, DateTime $from, DateTime $to): int
    {
        $clauses = [
            "o.created_at BETWEEN '{$from->format('Y-m-d H:i:s')}' AND '{$to->format('Y-m-d H:i:s')}'",
        ];

        $this->addClausesBySource($clauses, $source);

        $query = $this->createCountQuery(
            $source,
            $product::getProviderName(),
            $product->getIdentifier(),
            $clauses
        );

        return $this->executeQuery($query);
    }

    /**
     * Calculates the product sold quantity for the given group and date range.
     */
    public function calculateCountByGroup(
        Product  $product,
        string   $source,
        Group    $group,
        DateTime $from,
        DateTime $to
    ): int {
        $clauses = [
            "o.created_at BETWEEN '{$from->format('Y-m-d H:i:s')}' AND '{$to->format('Y-m-d H:i:s')}'",
            "o.customer_group_id = {$group->getId()}",
        ];

        $this->addClausesBySource($clauses, $source);

        $query = $this->createCountQuery(
            $source,
            $product::getProviderName(),
            $product->getIdentifier(),
            $clauses
        );

        return $this->executeQuery($query);
    }

    private function createCountQuery(string $source, string $provider, int $identifier, array $clauses): string
    {
        array_unshift($clauses, 'iq.parent_id IS NULL');
        $clauses = implode(' AND ', $clauses);

        return <<<SQL
            WITH RECURSIVE item_quantity (id, parent_id, {$source}_id, total) AS (
              SELECT id, parent_id, {$source}_id, quantity as total
              FROM commerce_{$source}_item
              WHERE subject_provider = '$provider'
                AND subject_identifier = $identifier
              UNION ALL
              SELECT i1.id, i1.parent_id, i1.{$source}_id, i2.total * i1.quantity
              FROM item_quantity AS i2
              JOIN commerce_{$source}_item AS i1 ON i2.parent_id = i1.id
            )
            SELECT SUM(iq.total) as quantity
            FROM item_quantity AS iq
            JOIN commerce_$source o ON iq.{$source}_id = o.id
            WHERE $clauses
            SQL;
    }

    private function executeQuery(string $query): int
    {
        $statement = $this->connection->executeQuery($query);

        if (false !== $row = $statement->fetchNumeric()) {
            return (int)$row[0];
        }

        return 0;
    }

    /**
     * Calculates the product cross-selling for the given date range.
     *
     * @param Product  $product
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return array
     */
    public function calculateCross(Product $product, DateTime $from, DateTime $to): array
    {
        $query =
            "WITH RECURSIVE item_quantity (id, subject_provider, subject_identifier, total) AS (
  SELECT id, subject_provider, subject_identifier, quantity as total
  FROM commerce_order_item
  WHERE parent_id IS NULL
    AND private = 0
    AND subject_provider = 'product'
    AND subject_identifier IS NOT NULL
    AND subject_identifier != {$product->getIdentifier()}
    AND order_id IN (
      WITH RECURSIVE item_order (parent_id, order_id) AS (
        SELECT parent_id, order_id
        FROM commerce_order_item
        WHERE subject_provider = 'product'
          AND subject_identifier = {$product->getIdentifier()}
        UNION ALL
        SELECT i1.parent_id, i1.order_id
        FROM item_order AS i2
        JOIN commerce_order_item AS i1 ON i2.parent_id = i1.id
      )
      SELECT iq.order_id
      FROM item_order AS iq
      JOIN commerce_order o ON iq.order_id = o.id
      WHERE iq.parent_id IS NULL
        AND o.created_at BETWEEN '{$from->format('Y-m-d H:i:s')}' AND '{$to->format('Y-m-d H:i:s')}'
        AND o.is_sample=0
        AND o.state IN ('accepted', 'completed')
    )
  UNION ALL
  SELECT i.id, i.subject_provider, i.subject_identifier, iq.total * i.quantity
  FROM item_quantity AS iq
  JOIN commerce_order_item AS i ON iq.id = i.parent_id
  WHERE i.subject_provider = 'product'
    AND i.subject_identifier != {$product->getIdentifier()}
)
SELECT r.subject_identifier as identifier, SUM(r.total) as quantity
FROM item_quantity AS r
GROUP BY r.subject_identifier
ORDER BY quantity DESC
LIMIT 24;"; // TODO WTF is this limit ?

        $statement = $this->connection->executeQuery($query);

        $result = [];
        while (false !== $data = $statement->fetchAssociative()) {
            $result[$data['identifier']] = (int)$data['quantity'];
        }

        return $result;
    }

    /**
     * Calculates the product cross-selling for the given group and date range.
     *
     * @param Product  $product
     * @param Group    $group
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return array
     */
    public function calculateCrossByGroup(Product $product, Group $group, DateTime $from, DateTime $to): array
    {
        $query =
            "WITH RECURSIVE item_quantity (id, subject_provider, subject_identifier, total) AS (
  SELECT id, subject_provider, subject_identifier, quantity as total
  FROM commerce_order_item
  WHERE parent_id IS NULL
    AND private = 0
    AND subject_provider = 'product'
    AND subject_identifier IS NOT NULL
    AND subject_identifier != {$product->getIdentifier()}
    AND order_id IN (
      WITH RECURSIVE item_order (parent_id, order_id) AS (
        SELECT parent_id, order_id
        FROM commerce_order_item
        WHERE subject_provider = 'product'
          AND subject_identifier = {$product->getIdentifier()}
        UNION ALL
        SELECT i1.parent_id, i1.order_id
        FROM item_order AS i2
        JOIN commerce_order_item AS i1 ON i2.parent_id = i1.id
      )
      SELECT iq.order_id
      FROM item_order AS iq
      JOIN commerce_order o ON iq.order_id = o.id
      WHERE iq.parent_id IS NULL
        AND o.created_at BETWEEN '{$from->format('Y-m-d H:i:s')}' AND '{$to->format('Y-m-d H:i:s')}'
        AND o.customer_group_id = {$group->getId()}
        AND o.is_sample=0
        AND o.state IN ('accepted', 'completed')
    )
  UNION ALL
  SELECT i.id, i.subject_provider, i.subject_identifier, iq.total * i.quantity
  FROM item_quantity AS iq
  JOIN commerce_order_item AS i ON iq.id = i.parent_id
  WHERE i.subject_provider = 'product'
    AND i.subject_identifier != {$product->getIdentifier()}
)
SELECT r.subject_identifier as identifier, SUM(r.total) as quantity
FROM item_quantity AS r
GROUP BY r.subject_identifier
ORDER BY quantity DESC
LIMIT 24;"; // TODO WTF is this limit ?

        $statement = $this->connection->executeQuery($query);

        $result = [];
        while (false !== $data = $statement->fetchAssociative()) {
            $result[$data['identifier']] = (int)$data['quantity'];
        }

        return $result;
    }

    /**
     * Returns the source dates.
     */
    public function getSourceDates(Product $product, Group $group, string $source): array
    {
        $clauses = [
            "o.customer_group_id = {$group->getId()}",
        ];

        $this->addClausesBySource($clauses, $source);

        $clauses = implode(' AND ', $clauses);

        // TODO Use recursive query ?
        $query = <<<SQL
        SELECT DATE_FORMAT(o.created_at, '%Y-%m') as date, MAX(o.updated_at) as updated_at FROM (
          SELECT
            IF(i1.{$source}_id IS NOT NULL, i1.{$source}_id,
              IF(i2.{$source}_id IS NOT NULL, i2.{$source}_id,
                IF(i3.{$source}_id IS NOT NULL, i3.{$source}_id,
                  IF(i4.{$source}_id IS NOT NULL, i4.{$source}_id, i5.{$source}_id)))) as {$source}_id
          FROM commerce_{$source}_item i1
          LEFT JOIN commerce_{$source}_item i2 ON i2.id = i1.parent_id
          LEFT JOIN commerce_{$source}_item i3 ON i3.id = i2.parent_id
          LEFT JOIN commerce_{$source}_item i4 ON i4.id = i3.parent_id
          LEFT JOIN commerce_{$source}_item i5 ON i5.id = i4.parent_id
          WHERE i1.subject_provider = 'product'
            AND i1.subject_identifier = {$product->getIdentifier()}
        ) as i
        JOIN commerce_$source o ON i.{$source}_id = o.id
        WHERE $clauses
        GROUP BY date;
        SQL;

        // TODO and o.created_at > -1 year

        $statement = $this->connection->executeQuery($query);

        $dates = [];
        while (false !== $data = $statement->fetchAssociative()) {
            $dates[$data['date']] = $data['updated_at'];
        }

        return $dates;
    }

    /**
     * Returns the stat count dates.
     */
    public function getStatCountDates(Product $product, Group $group, string $source): array
    {
        StatCount::isValidSource($source);

        $query = <<<SQL
        SELECT s.date, s.updated_at 
        FROM product_stat_count s 
        WHERE product_id = {$product->getIdentifier()} 
          AND s.group_id = {$group->getId()}
          AND s.source = '$source';
        SQL;

        // TODO and s.date >= -1 year

        $statement = $this->connection->executeQuery($query);

        $dates = [];
        while (false !== $data = $statement->fetchAssociative()) {
            $dates[$data['date']] = $data['updated_at'];
        }

        return $dates;
    }

    /**
     * Adds the 'where' clauses by source.
     */
    private function addClausesBySource(array &$clauses, string $source): void
    {
        StatCount::isValidSource($source);

        if ($source === StatCount::SOURCE_ORDER) {
            $clauses[] = "o.is_sample=0 AND o.state IN ('accepted', 'completed')";
        }
    }
}

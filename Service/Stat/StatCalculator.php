<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use Doctrine\DBAL\Connection;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface as Product;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface as Group;

/**
 * Class StatCalculator
 * @package Ekyna\Bundle\ProductBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Use ProductProvider::NAME instead of 'product' in queries
 */
class StatCalculator
{
    /**
     * @var Connection
     */
    private $connection;


    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Calculates the product sold quantity for the given date range.
     *
     * @param Product   $product
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return int
     */
    public function calculateCount(Product $product, \DateTime $from, \DateTime $to)
    {
        $query =
"WITH RECURSIVE item_quantity (id, parent_id, order_id, total) AS (
  SELECT id, parent_id, order_id, quantity as total
  FROM commerce_order_item
  WHERE subject_provider = 'product'
    AND subject_identifier = {$product->getIdentifier()}
  UNION ALL
  SELECT i1.id, i1.parent_id, i1.order_id, i2.total * i1.quantity
  FROM item_quantity AS i2
  JOIN commerce_order_item AS i1 ON i2.parent_id = i1.id
)
SELECT SUM(iq.total) as quantity
FROM item_quantity AS iq
 JOIN commerce_order o ON iq.order_id = o.id
WHERE iq.parent_id IS NULL
  AND o.created_at BETWEEN '{$from->format('Y-m-d H:i:s')}' AND '{$to->format('Y-m-d H:i:s')}'
  AND o.is_sample=0
  AND o.state IN ('accepted', 'completed');";

        $statement = $this->connection->query($query);
        if (false !== $data = $statement->fetch(\PDO::FETCH_ASSOC)) {
            return (int)$data['quantity'];
        }

        return 0;
    }

    /**
     * Calculates the product sold quantity for the given group and date range.
     *
     * @param Product   $product
     * @param Group     $group
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return int
     */
    public function calculateCountByGroup(Product $product, Group $group, \DateTime $from, \DateTime $to)
    {
        $query =
"WITH RECURSIVE item_quantity (id, parent_id, order_id, total) AS (
  SELECT id, parent_id, order_id, quantity as total
  FROM commerce_order_item
  WHERE subject_provider = 'product'
    AND subject_identifier = {$product->getIdentifier()}
  UNION ALL
  SELECT i1.id, i1.parent_id, i1.order_id, i2.total * i1.quantity
  FROM item_quantity AS i2
  JOIN commerce_order_item AS i1 ON i2.parent_id = i1.id
)
SELECT SUM(iq.total) as quantity
FROM item_quantity AS iq
 JOIN commerce_order o ON iq.order_id = o.id
WHERE iq.parent_id IS NULL
  AND o.created_at BETWEEN '{$from->format('Y-m-d H:i:s')}' AND '{$to->format('Y-m-d H:i:s')}'
  AND o.customer_group_id = {$group->getId()}
  AND o.is_sample=0
  AND o.state IN ('accepted', 'completed');";

        $statement = $this->connection->query($query);
        if (false !== $data = $statement->fetch(\PDO::FETCH_ASSOC)) {
            return (int)$data['quantity'];
        }

        return 0;
    }

    /**
     * Calculates the product cross selling for the given date range.
     *
     * @param Product   $product
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return array
     */
    public function calculateCross(Product $product, \DateTime $from, \DateTime $to)
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
LIMIT 24;";

        $statement = $this->connection->query($query);

        $result = [];
        while (false !== $data = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $result[$data['identifier']] = $data['quantity'];
        }

        return $result;
    }

    /**
     * Calculates the product cross selling for the given group and date range.
     *
     * @param Product   $product
     * @param Group     $group
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return array
     */
    public function calculateCrossByGroup(Product $product, Group $group, \DateTime $from, \DateTime $to)
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
LIMIT 24;";

        $statement = $this->connection->query($query);

        $result = [];
        while (false !== $data = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $result[$data['identifier']] = $data['quantity'];
        }

        return $result;
    }

    /**
     * Returns the orders dates.
     *
     * @param Product $product
     * @param Group   $group
     *
     * @return array
     */
    public function getOrderDates(Product $product, Group $group)
    {
        $query = "
SELECT DATE_FORMAT(o.created_at, '%Y-%m') as date, MAX(o.updated_at) as updated_at FROM (
  SELECT
    IF(i1.order_id IS NOT NULL, i1.order_id,
      IF(i2.order_id IS NOT NULL, i2.order_id,
        IF(i3.order_id IS NOT NULL, i3.order_id,
          IF(i4.order_id IS NOT NULL, i4.order_id, i5.order_id)))) as order_id
  FROM commerce_order_item i1
  LEFT JOIN commerce_order_item i2 ON i2.id = i1.parent_id
  LEFT JOIN commerce_order_item i3 ON i3.id = i2.parent_id
  LEFT JOIN commerce_order_item i4 ON i4.id = i3.parent_id
  LEFT JOIN commerce_order_item i5 ON i5.id = i4.parent_id
  WHERE i1.subject_provider = 'product'
    AND i1.subject_identifier = {$product->getIdentifier()}
) as i
JOIN commerce_order o ON i.order_id = o.id
WHERE o.state IN ('accepted', 'completed')
  AND o.is_sample=0
  AND o.customer_group_id = {$group->getId()}
GROUP BY date;";

        // TODO and o.created_at > -1 year

        $statement = $this->connection->query($query);

        $dates = [];
        while (false !== $data = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $dates[$data['date']] = $data['updated_at'];
        }

        return $dates;
    }

    /**
     * Returns the stat count dates.
     *
     * @param Product $product
     * @param Group   $group
     *
     * @return array
     */
    public function getStatCountDates(Product $product, Group $group)
    {
        $query = "
SELECT s.date, s.updated_at 
FROM product_stat_count s 
WHERE product_id = {$product->getIdentifier()} 
  AND s.group_id = {$group->getId()};";

        // TODO and s.date >= -1 year

        $statement = $this->connection->query($query);

        $dates = [];
        while (false !== $data = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $dates[$data['date']] = $data['updated_at'];
        }

        return $dates;
    }
}

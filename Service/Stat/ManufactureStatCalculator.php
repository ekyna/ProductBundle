<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use DateTime;
use Doctrine\DBAL\Connection;
use Ekyna\Bundle\ProductBundle\Entity\StatCount;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface as Product;

use function implode;

/**
 * Class ManufactureStatCalculator
 * @package Ekyna\Bundle\ProductBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ManufactureStatCalculator
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * Calculates the product sold quantity for the given date range.
     */
    public function calculateCount(Product $product, DateTime $from, DateTime $to): int
    {
        $clauses = [
            "p.created_at BETWEEN '{$from->format('Y-m-d H:i:s')}' AND '{$to->format('Y-m-d H:i:s')}'",
        ];

        $query = $this->createCountQuery(
            $product::getProviderName(),
            $product->getIdentifier(),
            $clauses
        );

        return $this->executeQuery($query);
    }

    /**
     * Returns the source dates.
     */
    public function getSourceDates(Product $product): array
    {
        $query = <<<SQL
        SELECT DATE_FORMAT(p.created_at, '%Y-%m') as date, MAX(p.updated_at) as updated_at 
        FROM commerce_production p
        JOIN commerce_production_order po ON po.id = p.order_id
        WHERE po.subject_provider = 'product'
          AND po.subject_identifier = {$product->getIdentifier()}
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
    public function getStatCountDates(Product $product): array
    {
        $source = StatCount::SOURCE_MANUFACTURE;

        $query = <<<SQL
        SELECT s.date, s.updated_at 
        FROM product_stat_count s 
        WHERE product_id = {$product->getId()} 
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

    private function createCountQuery(string $provider, int $identifier, array $clauses): string
    {
        $clauses = implode(' AND ', $clauses);

        return <<<SQL
            SELECT SUM(p.quantity) as quantity
            FROM commerce_production AS p
            JOIN commerce_production_order AS po ON po.id = p.order_id
            WHERE po.subject_provider = '$provider'
              AND po.subject_identifier = $identifier
              AND $clauses
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
}

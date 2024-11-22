<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stock\Analysis;

use Decimal\Decimal;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Ekyna\Bundle\ProductBundle\Entity\Product;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use function array_combine;
use function array_diff_assoc;
use function array_keys;
use function array_map;
use function array_values;
use function gc_collect_cycles;
use function implode;
use function preg_match;

/**
 * Class Importer
 * @package Ekyna\Bundle\ProductBundle\Service\Stock\Analysis
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Importer
{
    private array $importData;

    private Query $productById;

    public function __construct(
        private readonly Connection             $connection,
        private readonly EntityManagerInterface $entityManager,
        private readonly string                 $productClass,
    ) {
    }

    public function importXls(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);

        $this->createQueries();

        $this->readProducts($spreadsheet->getActiveSheet());

        return $this->writeProducts();
    }

    private function createQueries(): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $this->productById = $qb
            ->from($this->productClass/** @type Product */, 'p')
            ->select('p')
            ->where('p.id = :id')
            ->getQuery()
            ->setMaxResults(1);
    }

    private function readProducts(Worksheet $sheet): void
    {
        $this->importData = [];

        $endRow = $sheet->getHighestDataRow();
        $rows = $sheet->getRowIterator(2, $endRow);

        foreach ($rows as $row) {
            if ($row->isEmpty()) { // Ignore empty rows
                continue;
            }

            $rowIndex = $row->getRowIndex();

            $this->readProduct($sheet, $rowIndex);
        }
    }

    private function readProduct(Worksheet $sheet, int $rowIndex): void
    {
        $mapping = [
            'reference'   => 1,
            'end_of_life' => 3,
            'stock_floor' => 4,
        ];

        $reference = (string)$sheet->getCell([$mapping['reference'], $rowIndex])->getValue();
        if (!preg_match('~^[0-9]+$~', $reference)) {
            return;
        }

        if (isset($this->importData[$reference])) {
            throw new Exception('Duplicate product: ' . $reference);
        }

        $endOfLife = $sheet->getCell([$mapping['end_of_life'], $rowIndex])->getValue();
        $endOfLife = match ($endOfLife) {
            'EOL'   => 1,
            default => 0,
        };

        $stockFloor = (int)$sheet->getCell([$mapping['stock_floor'], $rowIndex])->getValue();

        $this->importData[$reference] = [
            'end_of_life' => $endOfLife,
            'stock_floor' => $stockFloor,
        ];
    }

    private function writeProducts(): array
    {
        $report = [];

        $page = -1;
        $size = 20;

        do {
            $page++;
            $rows = array_slice($this->importData, $page * $size, $size, true);
            if (empty($rows)) {
                break;
            }

            // Load raw data for comparison
            $references = implode(',', array_keys($rows));
            $data = $this->connection->executeQuery(
                <<<SQL
                SELECT p.id, p.reference, p.end_of_life, p.stock_floor
                FROM product_product p
                WHERE p.reference IN ($references)
                LIMIT $size
                SQL
            );
            $raw = [];
            foreach ($data as $datum) {
                $raw[$datum['reference']] = [
                    'id'          => (int)$datum['id'],
                    'reference'   => (string)$datum['reference'],
                    'end_of_life' => (int)$datum['end_of_life'],
                    'stock_floor' => (int)$datum['stock_floor'],
                ];
            }

            // Compare import with database and trigger update if needed
            $persist = false;
            foreach ($rows as $reference => $row) {
                if (!isset($raw[$reference])) {
                    $report[$reference] = [
                        'success'     => false,
                        'id'          => null,
                        'designation' => 'Not found',
                        'changes'     => [],
                    ];

                    continue;
                }

                $rawProduct = $raw[$reference];
                if (empty($diff = array_diff_assoc($row, $rawProduct))) {
                    continue;
                }

                $product = $this->selectProduct($rawProduct);

                if (isset($diff['end_of_life'])) {
                    $product->setEndOfLife((bool)$row['end_of_life']);
                }
                if (isset($diff['stock_floor'])) {
                    $product->setStockFloor(new Decimal($row['stock_floor']));
                }

                $this->entityManager->persist($product);
                $persist = true;

                $keys = array_keys($diff);
                $changes = array_combine($keys, array_map(function (string $key, int $value) use ($rawProduct) {
                    return [
                        'from' => $rawProduct[$key],
                        'to'   => $value,
                    ];
                }, $keys, array_values($diff)));

                $report[$reference] = [
                    'success'     => true,
                    'id'          => $product->getId(),
                    'designation' => (string)$product->getFullDesignation(true),
                    'changes'     => $changes,
                ];
            }

            if ($persist) {
                $this->entityManager->flush();
            }

            $this->entityManager->clear();
            gc_collect_cycles();
        } while (true);

        return $report;
    }

    private function selectProduct(array $raw): ProductInterface
    {
        $product = $this
            ->productById
            ->setParameter('id', $raw['id'])
            ->getOneOrNullResult();

        if ($product instanceof Product) {
            return $product;
        }

        throw new Exception('Product not found');
    }
}

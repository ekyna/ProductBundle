<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stock;

use DateTime;
use Doctrine\DBAL\Connection;
use Ekyna\Bundle\ProductBundle\Entity\Product;
use Ekyna\Bundle\ProductBundle\Entity\StatCount;
use Ekyna\Component\Resource\Helper\File\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

use function array_slice;
use function array_sum;
use function sprintf;
use function sys_get_temp_dir;

/**
 * Class AnalysisExport
 * @package Ekyna\Bundle\ProductBundle\Service\Stock
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AnalysisExporter
{
    private array $products;
    private array $forecast;
    private array $historic;

    public function __construct(
        private readonly StockRepository $stockRepository,
        private readonly Connection      $connection
    ) {
    }

    public function exportXls(string $fileName = null): string
    {
        if (empty($fileName)) {
            $fileName = sprintf(
                'stock_analysis_%s.xls',
                (new DateTime())->format('Y-m-d')
            );
        }

        $this->loadProducts();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 9,
            ],
        ]);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getDefaultRowDimension()->setRowHeight(18);

        $sheet->getRowDimension(1)->setRowHeight(33.75);
        $sheet->getColumnDimension('A')->setWidth(13.14);
        $sheet->getColumnDimension('B')->setWidth(68.29);
        $sheet->getColumnDimension('C')->setWidth(6);
        $sheet->getColumnDimension('D')->setWidth(6);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(9.57);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(11.43);
        $sheet->getColumnDimension('I')->setWidth(9.86);
        $sheet->getColumnDimension('J')->setWidth(9.86);
        $sheet->getColumnDimension('K')->setWidth(11.43);
        $sheet->getColumnDimension('L')->setWidth(11.43);
        $sheet->getColumnDimension('M')->setWidth(14);
        $sheet->getColumnDimension('N')->setWidth(14);
        $sheet->getColumnDimension('O')->setWidth(14);
        $sheet->getColumnDimension('P')->setWidth(14);

        $sheet->getStyle('A1:P1')->applyFromArray([
            'font'      => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'fill'      => [
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['argb' => 'FF666699'],
            ],
        ]);

        $sheet->getCell([1, 1])->setValue('Article');
        $sheet->getCell([2, 1])->setValue('Désignation article');
        $sheet->getCell([3, 1])->setValue('Statut');
        $sheet->getCell([4, 1])->setValue('Stock');
        $sheet->getCell([5, 1])->setValue('Qté cde client');
        $sheet->getCell([6, 1])->setValue('Achat DEV');
        $sheet->getCell([7, 1])->setValue('Dispo théorique');
        $sheet->getCell([8, 1])->setValue('Forecast sur 4 Mois');
        $sheet->getCell([9, 1])->setValue('Forecast sur 6 Mois');
        $sheet->getCell([10, 1])->setValue('Seuil stock mini');
        $sheet->getCell([11, 1])->setValue('Dernier mois');
        $sheet->getCell([12, 1])->setValue('3 derniers mois');
        $sheet->getCell([13, 1])->setValue('Entre 4 et 6 derniers mois');
        $sheet->getCell([14, 1])->setValue('Entre 7 et 9 derniers mois');
        $sheet->getCell([15, 1])->setValue('Entre 10 et 12 derniers mois');
        $sheet->getCell([16, 1])->setValue('Avant les 12 derniers mois');

        $altRowStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['argb' => 'FFF8FBFC'],
            ],
        ];

        $row = 1;
        foreach ($this->products as $product) {
            $row++;
            $id = $product['id'];
            $status = $product['end_of_life'] ? 'EOL' : '';

            $sheet->getCell([1, $row])->setValue($product['reference']);                      // Article
            $sheet->getCell([2, $row])->setValue($product['designation']);                    // Désignation article
            $sheet->getCell([3, $row])->setValue($status);                                    // Statut
            $sheet->getCell([4, $row])->setValue($product['in_stock']);                       // Stock
            $sheet->getCell([5, $row])->setValue($product['sold'] - $product['shipped']);     // Qté cde client
            // TODO what about pending ?
            $sheet->getCell([6, $row])->setValue($product['ordered'] - $product['received']); // Achat DEV
            $sheet->getCell([7, $row])->setValue($product['virtual_stock']);                  // Dispo théorique
            $sheet->getCell([8, $row])->setValue($this->getForecast($id, 4));                 // Forecast sur 4 Mois
            $sheet->getCell([9, $row])->setValue($this->getForecast($id, 6));                 // Forecast sur 6 Mois
            $sheet->getCell([10, $row])->setValue($product['stock_floor']);                   // Seuil stock mini
            $sheet->getCell([11, $row])->setValue($this->getHistoric($id, 0, 1));             // Dernier mois
            $sheet->getCell([12, $row])->setValue($this->getHistoric($id, 0, 3));             // 3 derniers mois
            $sheet->getCell([13, $row])->setValue($this->getHistoric($id, 3, 2));             // Entre 4 et 6 derniers mois
            $sheet->getCell([14, $row])->setValue($this->getHistoric($id, 6, 2));             // Entre 7 et 9 derniers mois
            $sheet->getCell([15, $row])->setValue($this->getHistoric($id, 9, 2));             // Entre 10 et 12 derniers mois
            $sheet->getCell([16, $row])->setValue($this->getHistoric($id, 11, null));         // Avant les 12 derniers mois

            if (1 === $row % 2) {
                $sheet->getStyle([1, $row, 16, $row])->applyFromArray($altRowStyle);
            }
        }

        // Center numbers
        $sheet->getStyle([4, 2, 16, $row])->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        // Forecast columns
        $sheet->getStyle([8, 1, 9, $row])->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['argb' => 'FFD8D8D8'],
            ],
            'font' => [
                'color' => ['argb' => 'FF000000'],
            ],
        ]);
        // Set all borders
        $sheet->getStyle([1, 1, 16, $row])->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFCCCCFF'],
                ],
            ],
        ]);

        $sheet->setAutoFilter('A1:C2');
        $sheet->getAutoFilter()->setRangeToMaxRow();

        $sheet->freezePane('A2');

        $writer = new Xls($spreadsheet);
        $path = sprintf('%s/%s', sys_get_temp_dir(), $fileName);
        $writer->save($path);

        return $path;
    }

    public function exportCsv(string $fileName = null): string
    {
        $this->loadProducts();

        if (empty($fileName)) {
            $fileName = sprintf(
                'stock_analysis_%s.csv',
                (new DateTime())->format('Y-m-d')
            );
        }

        $file = Csv::create($fileName);

        $file->addRow([
            'Article',
            'Désignation article',
            'Statut',
            'Stock',
            'Qté cde client',
            'Achat DEV',
            'Dispo théorique',
            'Forecast sur 4 Mois',
            'Forecast sur 6 Mois',
            'Seuil stock mini',
            'Dernier mois',
            '3 derniers mois',
            'Entre 4 et 6 derniers mois',
            'Entre 7 et 9 derniers mois',
            'Entre 10 et 12 derniers mois',
            'Avant les 12 derniers mois',
        ]);

        foreach ($this->products as $product) {
            $id = $product['id'];
            $status = $product['end_of_life'] ? 'EOL' : '';

            $file->addRow([
                $product['reference'], // Article
                $product['designation'], // Désignation article
                $status, // Statut
                $product['in_stock'], // Stock
                $product['sold'] - $product['shipped'], // Qté cde client
                $product['ordered'] - $product['received'], // Achat DEV // TODO what about pending ?
                $product['virtual_stock'], // Dispo théorique
                $this->getForecast($id, 4), // Forecast sur 4 Mois
                $this->getForecast($id, 6), // Forecast sur 6 Mois
                $product['stock_floor'], // Seuil stock mini
                $this->getHistoric($id, 0, 1), // Dernier mois
                $this->getHistoric($id, 0, 3), // 3 derniers mois
                $this->getHistoric($id, 3, 2), // Entre 4 et 6 derniers mois
                $this->getHistoric($id, 6, 2), // Entre 7 et 9 derniers mois
                $this->getHistoric($id, 9, 2), // Entre 10 et 12 derniers mois
                $this->getHistoric($id, 11, null), // Avant les 12 derniers mois
            ]);
        }

        return $file->close();
    }

    private function getForecast(int $id, int $nbMonths): int
    {
        if (!isset($this->forecast[$id])) {
            return 0;
        }

        $interval = array_slice($this->forecast[$id], 0, $nbMonths);

        return array_sum($interval);
    }

    private function getHistoric(int $id, int $fromMonth, ?int $nbMonths): int
    {
        if (!isset($this->historic[$id])) {
            return 0;
        }

        $interval = array_slice($this->historic[$id], $fromMonth, $nbMonths);

        return array_sum($interval);
    }

    private function loadProducts(): void
    {
        $qb = $this->stockRepository->getProductsQueryBuilder();
        $ex = $qb->expr();

        $this->products = $qb
            ->andWhere(
                $ex->not(
                    $ex->andX(
                        $ex->eq('p.endOfLife', 1),
                        $ex->eq('p.inStock', 0),
                        $ex->lt('p.virtualStock', 0)
                    )
                )
            )
            ->addOrderBy('p.reference')
            ->getQuery()
            ->getScalarResult();

        foreach ($this->products as &$product) {
            $this->stockRepository->normalizeProduct($product);

            unset($product);
        }

        $this->loadForecast();
        $this->loadStats();
    }

    private function loadForecast(): void
    {
        $trust = 5;
        $nbMonths = 6;

        $sql = <<<SQL
        WITH RECURSIVE item_quantity (id, parent_id, quote_id, total, subject_identifier) AS (
            SELECT id, parent_id, quote_id, quantity as total, subject_identifier
            FROM commerce_quote_item
            WHERE subject_provider = :provider
            UNION ALL
            SELECT i1.id, i1.parent_id, i1.quote_id, i2.total * i1.quantity, i2.subject_identifier
            FROM item_quantity AS i2
            JOIN commerce_quote_item AS i1 ON i2.parent_id = i1.id
        )
        SELECT iq.subject_identifier, DATE_FORMAT(o.project_date, '%Y-%m') as date, SUM(iq.total) as quantity
        FROM item_quantity AS iq
        JOIN commerce_quote o ON iq.quote_id = o.id
        WHERE iq.parent_id IS NULL
          AND o.project_alive = 1
          AND o.project_trust >= :trust
          AND o.project_date BETWEEN :from AND :to
        GROUP BY iq.subject_identifier, YEAR(o.project_date), MONTH(o.project_date)
        ORDER BY iq.subject_identifier, date;
        SQL;

        $statement = $this->connection->prepare($sql);

        $from = new DateTime();
        $to = new DateTime(sprintf('last day of +%d months', $nbMonths - 1));

        $data = $statement->executeQuery([
            'provider' => Product::getProviderName(),
            'trust'    => $trust,
            'from'     => $from->format('Y-m-d'),
            'to'       => $to->format('Y-m-d'),
        ]);

        $this->forecast = [];
        $defaults = $this->buildDefaults($nbMonths, '+1');

        while (false !== $row = $data->fetchAssociative()) {
            $id = (int)$row['subject_identifier'];
            if (!isset($this->forecast[$id])) {
                $this->forecast[$id] = $defaults;
            }

            $this->forecast[$id][$row['date']] = (int)$row['quantity'];
        }
    }

    private function loadStats(): void
    {
        $nbMonths = 12;

        $sql = <<<SQL
        SELECT product_id, date, count
        FROM product_stat_count
        WHERE source=:source
        GROUP BY product_id, date
        ORDER BY product_id, date DESC
        SQL;

        $statement = $this->connection->prepare($sql);

        $data = $statement->executeQuery([
            'source' => StatCount::SOURCE_ORDER,
        ]);

        $this->historic = [];
        $defaults = $this->buildDefaults($nbMonths, '-1');

        while (false !== $row = $data->fetchAssociative()) {
            $id = (int)$row['product_id'];
            if (!isset($this->historic[$id])) {
                $this->historic[$id] = $defaults;
            }

            $this->historic[$id][$row['date']] = (int)$row['count'];
        }
    }

    private function buildDefaults(int $count, string $step): array
    {
        $map = [];

        $date = new DateTime();
        for ($m = 0; $m < $count; $m++) {
            $map[$date->format('Y-m')] = 0;
            $date->modify($step . ' month');
        }

        return $map;
    }
}

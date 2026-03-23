<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stock\Analysis;

use DateTime;
use Doctrine\DBAL\Connection;
use Ekyna\Bundle\ProductBundle\Entity\Product;
use Ekyna\Bundle\ProductBundle\Entity\StatCount;
use Ekyna\Bundle\ProductBundle\Service\Stock\StockRepository;
use Ekyna\Component\Resource\Helper\File\Csv;
use PhpOffice\PhpSpreadsheet\Exception;
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
 * Class Exporter
 * @package Ekyna\Bundle\ProductBundle\Service\Stock\Analysis
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Exporter
{
    private const COLUMN_HEADER_STYLE = [
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
    ];

    private const COLUMN_EDITABLE_HEADER_STYLE = [
        'fill'      => [
            'color'    => ['argb' => 'FF669966'],
        ],
    ];

    private const ALT_ROW_STYLE = [
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color'    => ['argb' => 'FFF8FBFC'],
        ],
    ];

    private const CENTER_STYLE = [
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
        ],
    ];
    private const GREY_COLUMN_STYLE = [
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color'    => ['argb' => 'FFD8D8D8'],
        ],
        'font' => [
            'color' => ['argb' => 'FF000000'],
        ],
    ];
    private const BORDERS_STYLE = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['argb' => 'FFCCCCFF'],
            ],
        ],
    ];

    private array $products;
    private array $forecast;
    private array $historic;
    private array $supply;
    private array $shipment;

    public function __construct(
        private readonly StockRepository $stockRepository,
        private readonly Connection      $connection
    ) {
    }

    /**
     * @throws Exception
     */
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

        $this->buildStockSheet($spreadsheet);

        $this->buildDataSheet($spreadsheet);

        $this->buildPriceSheet($spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);

        // TODO "Help" tab with VB code to highlight changed cells.
        /*
         * You can edit green columns and reimport the file to update the database.
         *
         * You must keep the column names and the order of the columns.
         * You must keep the sheet names and the order of the sheets.
         *
         * The following VB code highlights the changed cells.
         *
         * Private Sub Worksheet_Change(ByVal Target As Range)
         *     ' Cette ligne s'exécute à chaque modification dans la feuille.
         *     ' "Target" représente la ou les cellules qui viennent d'être modifiées.
         *
         *     ' On vérifie que la modification n'est pas vide
         *     If Not IsEmpty(Target) Then
         *         ' On applique la couleur orange à l'intérieur de la cellule modifiée
         *         Target.Interior.Color = RGB(255, 192, 0)
         *     End If
         * End Sub
         */

        $writer = new Xls($spreadsheet);
        $path = sprintf('%s/%s', sys_get_temp_dir(), $fileName);
        $writer->save($path);

        return $path;
    }

    /**
     * @throws Exception
     */
    private function buildStockSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCodeName('stock');
        $sheet->setTitle('Stock');
        $sheet->getDefaultRowDimension()->setRowHeight(18);

        // Column widths
        $sheet->getRowDimension(1)->setRowHeight(33.75);
        $sheet->getColumnDimension('A')->setWidth(13.14);   // Référence
        $sheet->getColumnDimension('B')->setWidth(68.29);   // Désignation
        $sheet->getColumnDimension('C')->setWidth(6);       // Statut
        $sheet->getColumnDimension('D')->setWidth(9.86);    // Seuil stock mini
        $sheet->getColumnDimension('E')->setWidth(6);       // Stock
        $sheet->getColumnDimension('F')->setWidth(12);      // Qté cde client
        $sheet->getColumnDimension('G')->setWidth(9.57);    // Achat DEV
        $sheet->getColumnDimension('H')->setWidth(14);      // Dispo théorique
        $sheet->getColumnDimension('I')->setWidth(11.43);   // Forecast sur 4 Mois
        $sheet->getColumnDimension('J')->setWidth(9.86);    // Forecast sur 6 Mois
        $sheet->getColumnDimension('K')->setWidth(11.43);   // Dernier mois
        $sheet->getColumnDimension('L')->setWidth(11.43);   // 3 derniers mois
        $sheet->getColumnDimension('M')->setWidth(14);      // Entre 4 et 6 derniers mois
        $sheet->getColumnDimension('N')->setWidth(14);      // Entre 7 et 9 derniers mois
        $sheet->getColumnDimension('O')->setWidth(14);      // Entre 10 et 12 derniers mois
        $sheet->getColumnDimension('P')->setWidth(14);      // Avant les 12 derniers mois
        $sheet->getColumnDimension('Q')->setWidth(24);      // Fournisseur dernier achat
        $sheet->getColumnDimension('R')->setWidth(8);       // Prix dernier achat
        $sheet->getColumnDimension('S')->setWidth(8);       // Devise dernier achat
        $sheet->getColumnDimension('T')->setWidth(22);      // Date dernier achat
        $sheet->getColumnDimension('U')->setWidth(22);      // Date dernière livraison client

        // Column headers
        $sheet->getStyle('A1:U1')->applyFromArray(self::COLUMN_HEADER_STYLE);
        // Editable column headers
        $sheet->getStyle('C1:D1')->applyFromArray(self::COLUMN_EDITABLE_HEADER_STYLE);

        // TODO translations
        $sheet->getCell('A1')->setValue('Référence');
        $sheet->getCell('B1')->setValue('Désignation');
        $sheet->getCell('C1')->setValue('Statut');
        $sheet->getCell('D1')->setValue('Seuil stock mini');
        $sheet->getCell('E1')->setValue('Stock');
        $sheet->getCell('F1')->setValue('Qté cde client');
        $sheet->getCell('G1')->setValue('Achat DEV');
        $sheet->getCell('H1')->setValue('Dispo théorique');
        $sheet->getCell('I1')->setValue('Forecast sur 4 Mois');
        $sheet->getCell('J1')->setValue('Forecast sur 6 Mois');
        $sheet->getCell('K1')->setValue('Dernier mois');
        $sheet->getCell('L1')->setValue('3 derniers mois');
        $sheet->getCell('M1')->setValue('Entre 4 et 6 derniers mois');
        $sheet->getCell('N1')->setValue('Entre 7 et 9 derniers mois');
        $sheet->getCell('O1')->setValue('Entre 10 et 12 derniers mois');
        $sheet->getCell('P1')->setValue('Avant les 12 derniers mois');
        $sheet->getCell('Q1')->setValue('Fournisseur dernier achat');
        $sheet->getCell('R1')->setValue('Prix dernier achat');
        $sheet->getCell('S1')->setValue('Devise dernier achat');
        $sheet->getCell('T1')->setValue('Date dernier achat');
        $sheet->getCell('U1')->setValue('Date dernière livraison client');

        $row = 1;
        foreach ($this->products as $product) {
            $row++;
            $id = $product['id'];
            $status = $product['end_of_life'] ? 'EOL' : '';

            $sheet->getCell("A$row")->setValue($product['reference']);                      // Référence
            $sheet->getCell("B$row")->setValue($product['designation']);                    // Désignation
            $sheet->getCell("C$row")->setValue($status);                                    // Statut
            $sheet->getCell("D$row")->setValue($product['stock_floor']);                    // Seuil stock mini
            $sheet->getCell("E$row")->setValue($product['in_stock']);                       // Stock
            $sheet->getCell("F$row")->setValue($product['sold'] - $product['shipped']);     // Qté cde client
            // TODO what about pending ?
            $sheet->getCell("G$row")->setValue($product['ordered'] - $product['received']); // Achat DEV
            $sheet->getCell("H$row")->setValue($product['virtual_stock']);                  // Dispo théorique
            $sheet->getCell("I$row")->setValue($this->getForecast($id, 4));                 // Forecast sur 4 Mois
            $sheet->getCell("J$row")->setValue($this->getForecast($id, 6));                // Forecast sur 6 Mois
            $sheet->getCell("K$row")->setValue($this->getHistoric($id, 0, 1));             // Dernier mois
            $sheet->getCell("L$row")->setValue($this->getHistoric($id, 0, 3));             // 3 derniers mois
            $sheet->getCell("M$row")->setValue($this->getHistoric($id, 3, 3));             // Entre 4 et 6 derniers mois
            $sheet->getCell("N$row")->setValue($this->getHistoric($id, 6, 3));             // Entre 7 et 9 derniers mois
            $sheet->getCell("O$row")->setValue($this->getHistoric($id, 9, 3));             // Entre 10 et 12 derniers mois
            $sheet->getCell("P$row")->setValue($this->getHistoric($id, 12, null));         // Avant les 12 derniers mois

            if (isset($this->supply[$id])) {
                $supply = $this->supply[$id];

                $sheet->getCell("Q$row")->setValue($supply['supplier']); // Fournisseur dernier achat
                $sheet->getCell("R$row")->setValue($supply['price']);    // Prix dernier achat
                $sheet->getCell("S$row")->setValue($supply['currency']); // Devise dernier achat
                $sheet->getCell("T$row")->setValue($supply['date']);     // Date dernier achat
            }

            if (isset($this->shipment[$id])) {
                $sheet->getCell("U$row")->setValue($this->shipment[$id]); // Date dernière livraison client
            }

            if (1 === $row % 2) {
                $sheet->getStyle("A$row:U$row")->applyFromArray(self::ALT_ROW_STYLE);
            }
        }

        // References as raw text
        $sheet->getStyle('A')->getNumberFormat()->setFormatCode('@');

        // Center numbers
        $sheet->getStyle("D2:p$row")->applyFromArray(self::CENTER_STYLE);
        // Forecast columns
        $sheet->getStyle("H1:I$row")->applyFromArray(self::GREY_COLUMN_STYLE);
        // Set all borders
        $sheet->getStyle("A1:U$row")->applyFromArray(self::BORDERS_STYLE);

        $sheet->setAutoFilter('A1:C2');
        $sheet->getAutoFilter()->setRangeToMaxRow();
        $sheet->freezePane('B2');
    }

    /**
     * @throws Exception
     */
    private function buildDataSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setCodeName('data');
        $sheet->setTitle('Data');
        $sheet->getDefaultRowDimension()->setRowHeight(18);

        // Column widths
        $sheet->getRowDimension(1)->setRowHeight(33.75);
        $sheet->getColumnDimension('A')->setWidth(13.14); // Référence
        $sheet->getColumnDimension('B')->setWidth(68.29); // Désignation
        $sheet->getColumnDimension('C')->setWidth(9);     // Weight
        $sheet->getColumnDimension('D')->setWidth(9);     // PackageWeight
        $sheet->getColumnDimension('E')->setWidth(9);     // HSCode
        $sheet->getColumnDimension('F')->setWidth(9);     // EAN
        $sheet->getColumnDimension('G')->setWidth(9);     // MPN

        // Column headers
        $sheet->getStyle('A1:G1')->applyFromArray(self::COLUMN_HEADER_STYLE);

        // TODO translations
        $sheet->getCell('A1')->setValue('Référence');
        $sheet->getCell('B1')->setValue('Désignation');
        $sheet->getCell('C1')->setValue('Poids');
        $sheet->getCell('D1')->setValue('Poids emballé');
        $sheet->getCell('E1')->setValue('HSCode');
        $sheet->getCell('F1')->setValue('EAN13');
        $sheet->getCell('G1')->setValue('MPN');

        // TODO Add product rows
    }

    /**
     * @throws Exception
     */
    private function buildPriceSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setCodeName('price');
        $sheet->setTitle('Price');
        $sheet->getDefaultRowDimension()->setRowHeight(18);

        // TODO Add cost, margin, etc

        // Column widths
        $sheet->getRowDimension(1)->setRowHeight(33.75);
        $sheet->getColumnDimension('A')->setWidth(13.14); // Référence
        $sheet->getColumnDimension('B')->setWidth(68.29); // Désignation
        $sheet->getColumnDimension('C')->setWidth(9);     // PV

        // Column headers
        $sheet->getStyle('A1:C1')->applyFromArray(self::COLUMN_HEADER_STYLE);
        // Editable column headers
        $sheet->getStyle('C1:C1')->applyFromArray(self::COLUMN_EDITABLE_HEADER_STYLE);

        // TODO translations
        $sheet->getCell('A1')->setValue('Référence');
        $sheet->getCell('B1')->setValue('Désignation');
        $sheet->getCell('C1')->setValue('PV HT');

        $row = 1;
        foreach ($this->products as $product) {
            $row++;

            $sheet->getCell("A$row")->setValue($product['reference']);                      // Référence
            $sheet->getCell("B$row")->setValue($product['designation']);                    // Désignation
            $sheet->getCell("C$row")->setValue($product['net_price']);                      // PV

            if (1 === $row % 2) {
                $sheet->getStyle("A$row:C$row")->applyFromArray(self::ALT_ROW_STYLE);
            }
        }

        // References as raw text
        $sheet->getStyle('A')->getNumberFormat()->setFormatCode('@');

        // Center numbers
        //$sheet->getStyle([4, 2, 16, $row])->applyFromArray(self::CENTER_STYLE);
        // Forecast columns
        //$sheet->getStyle([8, 1, 9, $row])->applyFromArray(self::ALT_ROW_STYLE);
        // Set all borders
        $sheet->getStyle("A1:C$row")->applyFromArray(self::BORDERS_STYLE);

        $sheet->setAutoFilter('A1:B2');
        $sheet->getAutoFilter()->setRangeToMaxRow();
        $sheet->freezePane('B2');
    }

    public function exportCsv(string $fileName = null): string
    {
        $this->loadProducts();

        if (empty($fileName)) {
            $fileName = sprintf(
                'stock_analysis_%s',
                (new DateTime())->format('Y-m-d')
            );
        }

        $file = new Csv($fileName);

        $file->addRow([
            'Référence',
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

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
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
        $this->loadSupplierOrders();
        $this->loadShipments();
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Exception
     */
    private function loadForecast(): void
    {
        $trust = 5;
        $nbMonths = 6;

        /** @noinspection SqlDialectInspection */
        $sql = <<<SQL
        WITH RECURSIVE item_quantity (id, parent_id, quote_id, total, subject_provider, subject_identifier) AS (
            SELECT id, parent_id, quote_id, quantity as total, subject_provider, subject_identifier
            FROM commerce_quote_item
            WHERE subject_provider = :provider
            UNION ALL
            SELECT i1.id, i1.parent_id, i1.quote_id, i2.total * i1.quantity, i2.subject_provider, i2.subject_identifier
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
        GROUP BY iq.subject_provider, iq.subject_identifier, YEAR(o.project_date), MONTH(o.project_date)
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

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function loadStats(): void
    {
        $nbMonths = 12;

        /** @noinspection SqlDialectInspection */
        $sql = <<<SQL
        SELECT product_id, date, SUM(count) as count
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

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function loadSupplierOrders(): void
    {
        /** @noinspection SqlDialectInspection */
        $sql = <<<SQL
        SELECT soi2.subject_identifier as product_id,
               s.name as supplier,
               soi2.net_price as price,
               c.code as currency,
               sd2.created_at as date
        FROM (
             SELECT soi1.subject_identifier as product_id, MAX(sd1.created_at) as delivery_date
             FROM commerce_supplier_delivery sd1
             JOIN commerce_supplier_delivery_item sdi1 ON sdi1.supplier_delivery_id = sd1.id
             JOIN commerce_supplier_order_item soi1 ON soi1.id = sdi1.supplier_order_item_id
             WHERE soi1.subject_provider = 'product'
               AND soi1.subject_identifier IS NOT NULL
             GROUP BY soi1.subject_identifier
        ) as last_deliveries
        JOIN commerce_supplier_order_item soi2 ON soi2.subject_identifier = last_deliveries.product_id
        JOIN commerce_supplier_order so2 ON so2.id = soi2.supplier_order_id
        JOIN commerce_supplier_delivery sd2 ON sd2.supplier_order_id = so2.id AND sd2.created_at = last_deliveries.delivery_date
        JOIN commerce_supplier s ON s.id = so2.supplier_id
        JOIN commerce_currency c ON c.id = so2.currency_id
        GROUP BY soi2.subject_identifier
        SQL;

        $statement = $this->connection->prepare($sql);

        $data = $statement->executeQuery();

        $this->supply = [];

        while (false !== $row = $data->fetchAssociative()) {
            $this->supply[(int)$row['product_id']] = $row;
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function loadShipments(): void
    {
        /** @noinspection SqlDialectInspection */
        $sql = <<<SQL
        SELECT oi1.subject_identifier as product_id, MAX(s1.shipped_at) as shipment_date
        FROM commerce_order_shipment s1
        JOIN commerce_order_shipment_item si1 ON si1.shipment_id = s1.id
        JOIN commerce_order_item oi1 ON oi1.id = si1.order_item_id
        WHERE oi1.subject_provider = 'product'
        GROUP BY oi1.subject_identifier;
        SQL;

        $statement = $this->connection->prepare($sql);

        $data = $statement->executeQuery();

        $this->shipment = [];

        while (false !== $row = $data->fetchAssociative()) {
            $this->shipment[(int)$row['product_id']] = $row['shipment_date'];
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

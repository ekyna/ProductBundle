<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Commerce\Report;

use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Commerce\Report\Model\ProductData;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Commerce\Report\Section\SectionInterface;
use Ekyna\Component\Commerce\Report\Writer\WriterInterface;
use Ekyna\Component\Commerce\Report\Writer\XlsWriter;
use Ekyna\Component\Commerce\Stock\Helper\StockSubjectQuantityHelper;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\Translation\TranslatableInterface;

use function array_walk;
use function Symfony\Component\Translation\t;
use function uasort;

/**
 * Class ProductsSection
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce\Report
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductsSection implements SectionInterface
{
    final public const NAME = 'products';

    /** @var array<int, array<int, ProductData>> */
    private array $data;
    /** @var array<int, array{reference: string, designation: string, brand: string, category: string}> */
    private array $subjects;
    /** @var array<int, string> */
    private array  $years;
    private string $year;

    private ?MarginCalculatorInterface $calculator = null;

    public function __construct(
        private readonly SubjectHelperInterface     $subjectHelper,
        private readonly StockSubjectQuantityHelper $quantityHelper,
        private readonly MarginCalculatorFactory    $factory,
    ) {
    }

    public function initialize(ReportConfig $config): void
    {
        $this->data = [];
        $this->subjects = [];
        $this->years = $config->range->getYears();
    }

    public function read(ResourceInterface $resource): void
    {
        if (!$resource instanceof OrderInterface) {
            throw new UnexpectedTypeException($resource, OrderInterface::class);
        }

        $this->year = $resource->getAcceptedAt()->format('Y');

        $this->calculator = $this->factory->create();

        $this->calculateOrderItems($resource->getItems());
    }

    private function calculateOrderItems(Collection $items): void
    {
        foreach ($items as $item) {
            $this->calculateOrderItem($item);

            $this->calculateOrderItems($item->getChildren());
        }
    }

    private function calculateOrderItem(OrderItemInterface $item): void
    {
        if ($item->isCompound() && !$item->hasPrivateChildren()) {
            return;
        }

        $soldTotal = $this->quantityHelper->calculateSoldQuantity($item);
        if ($soldTotal->isZero()) {
            return;
        }

        $margin = $this->calculator->calculateSaleItem($item);

        $reference = $item->getReference();

        $this->addSubject($item);

        if (!isset($this->data[$reference][$this->year])) {
            $this->data[$reference][$this->year] = new ProductData();
        }

        $data = $this->data[$reference][$this->year];

        $data->quantity += $soldTotal;
        $data->margin->merge($margin);
    }

    private function addSubject(OrderItemInterface $item): void
    {
        if (isset($this->subjects[$reference = $item->getReference()])) {
            return;
        }

        $this->subjects[$reference] = [
            'designation' => $item->getDesignation(),
            'brand'       => '',
            'category'    => '',
        ];

        $product = $this->subjectHelper->resolve($item, false);
        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->subjects[$reference]['designation'] = $product->getFullDesignation();

        if (ProductTypes::isVariantType($product)) {
            $product = $product->getParent();
        }

        $this->subjects[$reference]['brand'] = $product->getBrand()->getName();

        if (false === $category = $product->getCategories()->first()) {
            return;
        }

        $this->subjects[$reference]['category'] = $category->getName();
    }

    public function write(WriterInterface $writer): void
    {
        if ($writer instanceof XlsWriter) {
            $this->writeXls($writer);

            return;
        }

        throw new UnexpectedValueException('Unsupported writer');
    }

    private function writeXls(XlsWriter $writer): void
    {
        $sheet = $writer->createSheet('Products'); // TODO Trans

        $this->writeXlsHeaders($sheet);

        // Calculate total
        array_walk($this->data, function (array &$data) {
            $total = new Decimal(0);
            /** @var ProductData $datum */
            foreach ($data as $datum) {
                $total += $datum->margin->getRevenueProduct();
            }
            $data['total'] = $total;
        });

        // Sort by highest revenue
        uasort($this->data, function (array $a, array $b): int {
            return $b['total'] <=> $a['total'];
        });

        // Values
        $row = 2;
        foreach ($this->data as $reference => $years) {
            $row++;

            $subject = $this->subjects[$reference];

            // Row header
            $sheet->getCell([1, $row])->setValue($reference);
            $sheet->getCell([2, $row])->setValue($subject['designation']);
            $sheet->getCell([3, $row])->setValue($subject['brand']);
            $sheet->getCell([4, $row])->setValue($subject['category']);

            foreach ($this->years as $index => $year) {
                $col = 5 + $index * 6;

                $data = $years[$year] ?? new ProductData();

                // Left border
                $sheet->getCell([$col, $row])->getStyle()->applyFromArray(XlsWriter::STYLE_BORDER_LEFT);

                // Cells values
                $sheet->getCell([$col, $row])->setValue($data->quantity->toFixed());
                $sheet->getCell([$col + 1, $row])->setValue($data->margin->getRevenueProduct());
                $sheet->getCell([$col + 2, $row])->setValue($data->margin->getCostProduct());
                $sheet->getCell([$col + 3, $row])->setValue($data->margin->getCostSupply());
                $sheet->getCell([$col + 4, $row])->setValue($data->margin->getPercent(true));
                $sheet->getCell([$col + 5, $row])->setValue($data->margin->getPercent(false));
            }
        }
    }

    private function writeXlsHeaders(Worksheet $sheet): void
    {
        $headerStyle = XlsWriter::STYLE_BOLD + XlsWriter::STYLE_BACKGROUND;

        $columns = [
            'Reference'   => 20,
            'Designation' => 140,
            'Brand'       => 28,
            'Category'    => 30,
        ];

        $col = 1;
        foreach ($columns as $label => $width) {
            $sheet->getColumnDimensionByColumn($col)->setWidth($width, 'mm');

            $sheet->mergeCells([$col, 1, $col, 2]);
            $sheet->getCell([$col, 1])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col, 2])->getStyle()->applyFromArray($headerStyle + XlsWriter::STYLE_BORDER_BOTTOM);
            $sheet->getCell([$col, 1])->setValue($label);

            $col++;
        }

        $yearStyle =
            XlsWriter::STYLE_BOLD
            + XlsWriter::STYLE_CENTER
            + XlsWriter::STYLE_BACKGROUND
            + XlsWriter::STYLE_BORDER_LEFT;

        $base = $col;
        foreach ($this->years as $index => $year) {
            $col = $base + ($index * 4);

            // Year (merged cells)
            $sheet->mergeCells([$col, 1, $col + 5, 1]);
            $sheet->getCell([$col, 1])->getStyle()->applyFromArray($yearStyle);
            $sheet->getCell([$col, 1])->setValue($year);

            // Quantity
            $sheet->getColumnDimensionByColumn($col)->setWidth(18, 'mm');
            $sheet->getCell([$col, 2])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col, 2])->getStyle()->applyFromArray(XlsWriter::STYLE_BORDER_LEFT);
            $sheet->getCell([$col, 2])->setValue('Quantity'); // TODO Trans

            // Revenue product
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(20, 'mm');
            $sheet->getCell([$col + 1, 2])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 1, 2])->setValue('Sales'); // TODO Trans

            // Cost product
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(20, 'mm');
            $sheet->getCell([$col + 2, 2])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 2, 2])->setValue('Purchase'); // TODO Trans

            // Cost supply
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(20, 'mm');
            $sheet->getCell([$col + 3, 2])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 3, 2])->setValue('Supply'); // TODO Trans

            // Gross margin
            $sheet->getColumnDimensionByColumn($col + 2)->setWidth(22, 'mm');
            $sheet->getCell([$col + 4, 2])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 4, 2])->setValue('Marge Brut.'); // TODO Trans

            // Net margin
            $sheet->getColumnDimensionByColumn($col + 3)->setWidth(25, 'mm');
            $sheet->getCell([$col + 5, 2])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 5, 2])->setValue('Marge Net.'); // TODO Trans
        }
    }

    public function requiresResources(): array
    {
        return [OrderInterface::class];
    }

    public function supportsWriter(string $writerClass): bool
    {
        return $writerClass === XlsWriter::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTitle(): TranslatableInterface
    {
        return t('product.label.plural', [], 'EkynaProduct');
    }
}

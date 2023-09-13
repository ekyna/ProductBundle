<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Exporter;

use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\ProductBundle\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Bundle\ProductBundle\Model\ExportConfig;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PurchaseCostCalculator;
use Ekyna\Component\Resource\Helper\File\Csv;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_map;
use function join;

/**
 * Class ProductExporter
 * @package Ekyna\Bundle\ProductBundle\Service\Exporter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductExporter
{
    protected ExportConfig $config;
    protected Csv          $file;

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly PriceCalculator            $priceCalculator,
        private readonly PurchaseCostCalculator     $costCalculator,
        private readonly SubjectHelperInterface     $subjectHelper,
        private readonly TranslatorInterface        $translator
    ) {
    }

    /**
     * Exports products.
     */
    public function export(ExportConfig $config): Csv
    {
        $this->config = $config;

        $this->file = Csv::create('product_export.csv');
        $this->file->setSeparator($this->config->getSeparator());
        $this->file->setEnclosure($this->config->getEnclosure());

        $this->buildHeaders();
        $this->buildRows();

        return $this->file;
    }

    /**
     * Builds the headers.
     */
    private function buildHeaders(): void
    {
        $definitions = ExportConfig::getColumnsLabels();

        $headers = [];

        foreach ($this->config->getColumns() as $column) {
            $headers[] = $definitions[$column]->trans($this->translator);
        }

        $this->file->addRow($headers);
    }

    /**
     * Builds the product rows.
     */
    private function buildRows(): void
    {
        $products = $this->productRepository->findForExport($this->config);

        foreach ($products as $product) {
            $this->buildRow($product);
        }
    }

    /**
     * Builds the product row.
     */
    private function buildRow(ProductInterface $product): void
    {
        $row = [];

        $price = $this->priceCalculator->getPrice($product, $this->config->getContext());

        foreach ($this->config->getColumns() as $column) {
            $row[] = $this->buildCell($product, $price, $column);
        }

        $this->file->addRow($row);
    }

    /**
     * Builds the cell value.
     */
    protected function buildCell(ProductInterface $product, array $price, string $column): string
    {
        if ($column === ExportConfig::COLUMN_DESIGNATION) {
            return $product->getFullDesignation(true);
        }

        if ($column === ExportConfig::COLUMN_REFERENCE) {
            return $product->getReference();
        }

        if ($column === ExportConfig::COLUMN_EXT_EAN8) {
            return $product->getReferenceByType(ProductReferenceTypes::TYPE_EAN_8) ?: '';
        }

        if ($column === ExportConfig::COLUMN_EXT_EAN13) {
            return $product->getReferenceByType(ProductReferenceTypes::TYPE_EAN_13) ?: '';
        }

        if ($column === ExportConfig::COLUMN_EXT_MANUFACTURER) {
            return $product->getReferenceByType(ProductReferenceTypes::TYPE_MANUFACTURER) ?: '';
        }

        if ($column === ExportConfig::COLUMN_NET_PRICE) {
            return (string)($price['original_price'] ?? $price['sell_price']);
        }

        if ($column === ExportConfig::COLUMN_DISCOUNT) {
            return 0 < $price['percent'] ? (string)$price['percent'] : '';
        }

        if ($column === ExportConfig::COLUMN_SELL_PRICE) {
            return (string)$price['sell_price'];
        }

        if ($column === ExportConfig::COLUMN_BUY_PRICE) {
            return $this->costCalculator->calculateMinPurchaseCost($product)->getProduct()->toFixed(3);
        }

        if ($column === ExportConfig::COLUMN_BUY_PRICE_SHIP) {
            return $this->costCalculator->calculateMinPurchaseCost($product)->getTotal()->toFixed(3);
        }

        if ($column === ExportConfig::COLUMN_VALID_UNTIL) {
            return $this->config->getValidUntil()->format('Y-m-d');
        }

        if ($column === ExportConfig::COLUMN_WEIGHT) {
            return $product->getWeight()->toFixed(3);
        }

        if ($column === ExportConfig::COLUMN_WIDTH) {
            return (string)$product->getPackageWidth();
        }

        if ($column === ExportConfig::COLUMN_HEIGHT) {
            return (string)$product->getPackageHeight();
        }

        if ($column === ExportConfig::COLUMN_DEPTH) {
            return (string)$product->getPackageDepth();
        }

        if ($column === ExportConfig::COLUMN_DESCRIPTION) {
            $desc = $product->translate($this->config->getContext()->getLocale())->getDescription();
            $desc = str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $desc ?? '');

            return strip_tags($desc);
        }

        if ($column === ExportConfig::COLUMN_IMAGE) {
            return (string)$this->subjectHelper->generateImageUrl($product, false);
        }

        if ($column === ExportConfig::COLUMN_URL) {
            return (string)$this->subjectHelper->generatePublicUrl($product, false);
        }

        if ($column === ExportConfig::COLUMN_VISIBLE) {
            return $product->isVisible() ? 'Yes' : 'No';
        }

        if ($column === ExportConfig::COLUMN_QUOTE_ONLY) {
            return $product->isQuoteOnly() ? 'Yes' : 'No';
        }

        if ($column === ExportConfig::COLUMN_END_OF_LIFE) {
            return $product->isEndOfLife() ? 'Yes' : 'No';
        }

        throw new RuntimeException("Unexpected column name '$column'.");
    }
}

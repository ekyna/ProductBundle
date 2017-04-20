<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Exporter;

use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\ProductBundle\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Model\ExportConfig;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ProductExporter
 * @package Ekyna\Bundle\ProductBundle\Service\Exporter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductExporter
{
    private ProductRepositoryInterface $productRepository;
    private PriceCalculator            $priceCalculator;
    private SubjectHelperInterface     $subjectHelper;
    private TranslatorInterface        $translator;

    protected ExportConfig $config;
    /** @var resource */
    private $handle;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        PriceCalculator            $priceCalculator,
        SubjectHelperInterface     $subjectHelper,
        TranslatorInterface        $translator
    ) {
        $this->productRepository = $productRepository;
        $this->priceCalculator = $priceCalculator;
        $this->subjectHelper = $subjectHelper;
        $this->translator = $translator;
    }

    /**
     * Exports products.
     */
    public function export(ExportConfig $config): string
    {
        $this->config = $config;

        if (false === $path = tempnam(sys_get_temp_dir(), 'product_export')) {
            throw new RuntimeException('Failed to create temporary file.');
        }

        if (false === $this->handle = fopen($path, 'w')) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        $this->buildHeaders();
        $this->buildRows();

        fclose($this->handle);

        return $path;
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

        fputcsv($this->handle, $headers, $this->config->getSeparator(), $this->config->getEnclosure());
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

        foreach ($this->config->getColumns() as $column) {
            $price = $this->priceCalculator->getPrice($product, $this->config->getContext());

            $row[] = $this->buildCell($product, $price, $column);
        }

        fputcsv($this->handle, $row, $this->config->getSeparator(), $this->config->getEnclosure());
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

        if ($column === ExportConfig::COLUMN_NET_PRICE) {
            return (string)($price['original_price'] ?? $price['sell_price']);
        }

        if ($column === ExportConfig::COLUMN_DISCOUNT) {
            return 0 < $price['percent'] ? (string)$price['percent'] : '';
        }

        if ($column === ExportConfig::COLUMN_BUY_PRICE) {
            return (string)$price['sell_price'];
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
            $desc = str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $desc);

            return strip_tags($desc);
        }

        if ($column === ExportConfig::COLUMN_IMAGE) {
            return (string)$this->subjectHelper->generateImageUrl($product, false);
        }

        if ($column === ExportConfig::COLUMN_URL) {
            return (string)$this->subjectHelper->generatePublicUrl($product, false);
        }

        throw new RuntimeException("Unexpected column name '$column'.");
    }
}

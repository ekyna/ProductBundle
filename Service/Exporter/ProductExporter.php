<?php

namespace Ekyna\Bundle\ProductBundle\Service\Exporter;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Model\ExportConfig;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepository;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ProductExporter
 * @package Ekyna\Bundle\ProductBundle\Service\Exporter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductExporter
{
    /**
     * @var ExportConfig
     */
    protected $config;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var resource
     */
    private $handle;


    /**
     * Constructor.
     *
     * @param ProductRepository      $productRepository
     * @param PriceCalculator        $priceCalculator
     * @param SubjectHelperInterface $subjectHelper
     * @param TranslatorInterface    $translator
     */
    public function __construct(
        ProductRepository $productRepository,
        PriceCalculator $priceCalculator,
        SubjectHelperInterface $subjectHelper,
        TranslatorInterface $translator
    ) {
        $this->productRepository = $productRepository;
        $this->priceCalculator   = $priceCalculator;
        $this->subjectHelper     = $subjectHelper;
        $this->translator        = $translator;
    }

    /**
     * Exports products.
     *
     * @param ExportConfig $config
     *
     * @return string
     */
    public function export(ExportConfig $config): string
    {
        $this->config = $config;

        if (false === $path = tempnam(sys_get_temp_dir(), 'product_export')) {
            throw new RuntimeException("Failed to create temporary file.");
        }

        if (false === $this->handle = fopen($path, "w")) {
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
        $definitions = array_flip(ExportConfig::getColumnsChoices());

        $headers = [];

        foreach ($this->config->getColumns() as $column) {
            $headers[] = $this->translator->trans($definitions[$column]);
        }

        fputcsv($this->handle, $headers, $this->config->getSeparator(), $this->config->getEnclosure());
    }

    /**
     * Builds the product rows.
     */
    private function buildRows(): void
    {
        $products = $this->findProducts();

        foreach ($products as $product) {
            $this->buildRow($product);
        }
    }

    /**
     * Finds products.
     *
     * @return array
     */
    protected function findProducts(): array
    {
        $qb = $this->productRepository->createQueryBuilder('p');
        $qb
            ->join('p.brand', 'b')
            ->addOrderBy('b.name', 'ASC')
            ->addOrderBy('p.designation', 'ASC')
            ->andWhere($qb->expr()->notIn('p.type', ':types'))
            ->andWhere($qb->expr()->eq('p.quoteOnly', ':quote_only'))
            ->andWhere(
                $qb->expr()->not(
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.endOfLife', ':end_of_life'),
                        $qb->expr()->neq('p.stockState', ':stock_state')
                    )
                )
            )
            ->setParameters(
                [
                    'types'       => [
                        ProductTypes::TYPE_VARIANT,
                        ProductTypes::TYPE_CONFIGURABLE,
                    ],
                    'quote_only'  => false,
                    'end_of_life' => true,
                    'stock_state' => StockSubjectStates::STATE_IN_STOCK,
                ]
            );

        $this->applyConfigToQueryBuilder($qb);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * Applies the config to the query builder.
     *
     * @param QueryBuilder $qb
     */
    protected function applyConfigToQueryBuilder(QueryBuilder $qb): void
    {
        $brands = $this->config->getBrands();
        if (!$brands->isEmpty()) {
            $qb
                ->andWhere($qb->expr()->in('p.brand', ':brands'))
                ->setParameter('brands', $brands->toArray());
        }

        if ($this->config->isVisible()) {
            $qb
                ->andWhere($qb->expr()->eq('p.visible', ':visible'))
                ->setParameter('visible', true);
        }
    }

    /**
     * Builds the product row.
     *
     * @param ProductInterface $product
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
     *
     * @param ProductInterface $product
     * @param array            $price
     * @param string           $column
     *
     * @return string
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
            return $price['original_price'] ?? $price['sell_price'];
        }

        if ($column === ExportConfig::COLUMN_DISCOUNT) {
            return 0 < $price['percent'] ? $price['percent'] : '';
        }

        if ($column === ExportConfig::COLUMN_BUY_PRICE) {
            return $price['sell_price'];
        }

        if ($column === ExportConfig::COLUMN_VALID_UNTIL) {
            return $this->config->getValidUntil()->format('Y-m-d');
        }

        if ($column === ExportConfig::COLUMN_WEIGHT) {
            return $product->getWeight();
        }

        if ($column === ExportConfig::COLUMN_WIDTH) {
            return $product->getPackageWidth();
        }

        if ($column === ExportConfig::COLUMN_HEIGHT) {
            return $product->getPackageHeight();
        }

        if ($column === ExportConfig::COLUMN_DEPTH) {
            return $product->getPackageDepth();
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

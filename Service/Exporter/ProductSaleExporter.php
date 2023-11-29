<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Exporter;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Commerce\Report\Model\ProductData;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Helper\StockSubjectQuantityHelper;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Helper\File\Csv;
use Ekyna\Component\Resource\Model\DateRange;
use Psr\Log\LoggerInterface;

use function gc_collect_cycles;

/**
 * Class ProductSaleExporter
 * @package Ekyna\Bundle\ProductBundle\Service\Exporter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductSaleExporter
{
    private ?LoggerInterface $logger = null;

    private MarginCalculatorInterface $calculator;
    private array                     $products;
    /**
     * @var array<string, array<int, array<int, array<string, array<string, ProductData>>>>>
     */
    private array  $sales;
    private int    $year;
    private int    $month;
    private string $group;
    private string $customer;

    public function __construct(
        private readonly OrderRepositoryInterface   $repository,
        private readonly EntityManagerInterface     $manager,
        private readonly SubjectHelperInterface     $subjectHelper,
        private readonly StockSubjectQuantityHelper $quantityHelper,
        private readonly MarginCalculatorFactory    $factory,
    ) {
    }

    public function export(DateRange $range, LoggerInterface $logger = null): Csv
    {
        $this->logger = $logger;

        $this->loadData($range);

        return $this->buildCSV();
    }

    private function loadData(DateRange $range): void
    {
        $size = 30;
        $this->products = [];
        $this->sales = [];

        foreach ($range->byMonths() as $month) {
            $this->logger?->debug('Month ' . $month->getStart()->format('Y-m'));

            $page = 0;
            while (!empty($orders = $this->repository->findByAcceptedAt($month, $page, $size))) {
                foreach ($orders as $order) {
                    $this->logger?->debug((string)$order);

                    $this->readOrder($order);
                }

                $page++;
            }
        }

        $this->manager->clear();
        gc_collect_cycles();
    }

    private function readOrder(OrderInterface $order): void
    {
        $this->year = (int)$order->getAcceptedAt()->format('Y');
        $this->month = (int)$order->getAcceptedAt()->format('m');
        $this->group = $order->getCustomerGroup()->getName();
        $this->customer = $order->getCustomer()?->getCompany()
            ?? $order->getCompany()
            ?? 'Unknown';

        $this->calculator = $this->factory->create();

        $this->readOrderItems($order->getItems());
    }

    private function readOrderItems(Collection $items): void
    {
        foreach ($items as $item) {
            $this->readOrderItem($item);

            $this->readOrderItems($item->getChildren());
        }
    }

    private function readOrderItem(OrderItemInterface $item): void
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

        $this->addProduct($item);

        if (!isset($this->sales[$reference][$this->year][$this->month][$this->group][$this->customer])) {
            $this->sales[$reference][$this->year][$this->month][$this->group][$this->customer] = new ProductData();
        }

        $data = $this->sales[$reference][$this->year][$this->month][$this->group][$this->customer];

        $data->quantity += $soldTotal;
        $data->margin->merge($margin);
    }

    private function addProduct(OrderItemInterface $item): void
    {
        if (isset($this->products[$reference = $item->getReference()])) {
            return;
        }

        $this->products[$reference] = [
            'designation' => $item->getDesignation(),
            'brand'       => '',
            'category'    => '',
        ];

        $product = $this->subjectHelper->resolve($item, false);
        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->products[$reference]['designation'] = $product->getFullDesignation();

        if (ProductTypes::isVariantType($product)) {
            $product = $product->getParent();
        }

        $this->products[$reference]['brand'] = $product->getBrand()->getName();

        if (false === $category = $product->getCategories()->first()) {
            return;
        }

        $this->products[$reference]['category'] = $category->getName();
    }

    private function buildCSV(): Csv
    {
        $csv = Csv::create('product-sales.csv');

        $csv->addRow([
            'Reference',
            'Designation',
            'Brand',
            'Category',
            'Year',
            'Month',
            'Group customer',
            'Customer',
            'Quantity',
            'Revenue',
            'Cost',
            'Net margin amount',
            'Net Margin percent',
        ]);

        $gross = true;

        foreach ($this->sales as $reference => $years) {
            foreach ($years as $year => $months) {
                foreach ($months as $month => $groups) {
                    foreach ($groups as $group => $customers) {
                        foreach ($customers as $customer => $data) {
                            $product = $this->products[$reference];

                            $csv->addRow([
                                $reference,
                                $product['designation'],
                                $product['brand'],
                                $product['category'],
                                $year,
                                $month,
                                $group,
                                $customer,
                                $data->quantity->toFixed(),
                                $data->margin->getRevenueTotal($gross),
                                $data->margin->getCostTotal($gross),
                                $data->margin->getTotal($gross),
                                $data->margin->getPercent($gross),
                            ]);
                        }
                    }
                }
            }
        }

        return $csv;
    }
}

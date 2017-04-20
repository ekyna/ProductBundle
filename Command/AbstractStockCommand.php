<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractStockCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStockCommand extends Command
{
    protected ProductRepositoryInterface $repository;

    public function __construct(ProductRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Finds the product for the given id.
     */
    protected function findProduct(int $productId): ProductInterface
    {
        /** @var ProductInterface $product */
        $product = $this->repository->find($productId);
        if (null === $product) {
            throw new InvalidArgumentException('Product not found.');
        }

        return $product;
    }

    /**
     * Display the products stock table.
     *
     * @param array<ProductInterface> $products
     */
    protected function stockTable(OutputInterface $output, array $products): void
    {
        $table = new Table($output);
        $table->setHeaders(['Mode', 'State', 'In', 'Available', 'Virtual', 'EDA']);

        foreach ($products as $product) {
            $eda = $product->getEstimatedDateOfArrival();
            if (null !== $eda) {
                $eda = $eda->format('d-m-Y');
            }

            $table->addRow([
                $product->getStockMode(),
                $product->getStockState(),
                $product->getInStock()->toFixed(3),
                $product->getAvailableStock()->toFixed(3),
                $product->getVirtualStock()->toFixed(3),
                $eda,
            ]);
        }

        $table->render();
    }
}

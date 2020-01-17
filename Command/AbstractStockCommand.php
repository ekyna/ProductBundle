<?php

namespace Ekyna\Bundle\ProductBundle\Command;

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
    /**
     * @var ProductRepositoryInterface
     */
    private $repository;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(ProductRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Finds the product for the given id.
     *
     * @param $productId
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\ProductInterface
     */
    protected function findProduct($productId)
    {
        $productId = intval($productId);
        if (0 >= $productId) {
            throw new InvalidArgumentException("Please provide an integer greater than zero as product id.");
        }

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
        $product = $this->repository->find($productId);
        if (null === $product) {
            throw new InvalidArgumentException("Product with id $productId not found.");
        }

        return $product;
    }

    /**
     * Display the products stock table.
     *
     * @param OutputInterface                                      $output
     * @param \Ekyna\Bundle\ProductBundle\Model\ProductInterface[] $products
     */
    protected function stockTable(OutputInterface $output, array $products)
    {
        $table = new Table($output);
        $table->setHeaders(array('Mode', 'State', 'In', 'Available', 'Virtual', 'EDA'));

        foreach ($products as $product) {
            $eda = $product->getEstimatedDateOfArrival();
            if (null !== $eda) {
                $eda = $eda->format('d-m-Y');
            }

            $table->addRow([
                $product->getStockMode(),
                $product->getStockState(),
                $product->getInStock(),
                $product->getAvailableStock(),
                $product->getVirtualStock(),
                $eda
            ]);
        }

        $table->render();
    }
}

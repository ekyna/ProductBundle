<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StockUpdateCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUpdateCommand extends AbstractStockCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:product:stock:update')
            ->setDescription("Updates the product stock.")
            ->addArgument('id', InputArgument::REQUIRED, "The product's id to update.");
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $product = $this->findProduct($input->getArgument('id'));

        $product->setInStock(0);

        $this->getContainer()->get('ekyna_product.product.operator')->update($product);

        $this->stockTable($output, [$product]);
    }
}

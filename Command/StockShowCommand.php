<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StockShowCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockShowCommand extends AbstractStockCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:product:stock:show')
            ->setDescription("Shows the product stock.")
            ->addArgument('id', InputArgument::REQUIRED, "The product's id to update.");
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $product = $this->findProduct($input->getArgument('id'));

        $this->stockTable($output, [$product]);
    }
}

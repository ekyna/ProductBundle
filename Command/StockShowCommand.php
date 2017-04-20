<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use Symfony\Component\Console\Command\Command;
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
    protected static $defaultName = 'ekyna:product:stock:show';

    protected function configure(): void
    {
        $this
            ->setDescription('Shows the product stock.')
            ->addArgument('id', InputArgument::REQUIRED, "The product's id to update.");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $product = $this->findProduct($input->getArgument('id'));

        $this->stockTable($output, [$product]);

        return Command::SUCCESS;
    }
}

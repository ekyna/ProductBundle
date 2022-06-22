<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

/**
 * Class ClearPastEDACommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ClearPastEDACommand extends Command
{
    protected static $defaultName = 'ekyna:product:product:clear_past_eda';

    private ProductRepositoryInterface   $repository;
    private StockSubjectUpdaterInterface $updater;
    private ResourceManagerInterface     $manager;

    public function __construct(
        ProductRepositoryInterface   $repository,
        StockSubjectUpdaterInterface $updater,
        ResourceManagerInterface     $manager
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->updater = $updater;
        $this->manager = $manager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $noDebug = $input->getOption('no-debug');

        $products = $this->repository->findHavingPastEDA();

        if (empty($products)) {
            return Command::SUCCESS;
        }

        foreach ($products as $product) {
            $old = $product->getEstimatedDateOfArrival();
            if (!$this->updater->update($product)) {
                continue;
            }

            if (!$noDebug) {
                $new = $product->getEstimatedDateOfArrival();
                $output->writeln(sprintf(
                    '<comment>%s</comment>',
                    $product
                ));
                $output->writeln(sprintf(
                    '%s -> %s',
                    $old ? $old->format('Y-m-d') : 'none',
                    $new ? $new->format('Y-m-d') : 'none',
                ));
            }

            $this->manager->persist($product);
            $this->manager->flush();
        }

        return Command::SUCCESS;
    }
}

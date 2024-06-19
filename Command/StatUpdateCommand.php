<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use DateTime;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Stat\StatUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function intval;
use function usleep;

/**
 * Class StatUpdateCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatUpdateCommand extends Command
{
    protected static $defaultName = 'ekyna:product:stat:update';

    private OutputInterface $output;
    private bool            $debug;

    public function __construct(
        private readonly ProductRepositoryInterface $repository,
        private readonly StatUpdater                $updater,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Updates the products stats')
            ->addArgument('id', InputArgument::OPTIONAL, 'The id of the product to update')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Whether to force update')
            ->addOption('purge', null, InputOption::VALUE_NONE, 'Whether to purge stats first')
            ->addOption(
                'interval',
                null,
                InputOption::VALUE_REQUIRED,
                'The interval between 2 updates for a given product in hours',
                6
            )
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'The maximum execution time in seconds', 120)
            ->addOption(
                'breathe',
                null,
                InputOption::VALUE_REQUIRED,
                'Delay between each product update in milliseconds',
                0
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->updater->setOutput($this->output = $output);
        $this->updater->setDebug($this->debug = !$input->getOption('no-debug'));
        $this->updater->setForce((bool)$input->getOption('force'));

        if (0 < $id = (int)$input->getArgument('id')) {
            if (null === $product = $this->repository->find($id)) {
                $output->writeln("Product #$id not found.");

                return Command::FAILURE;
            }

            $this->updater->update($product);

            return Command::SUCCESS;
        }

        if ($input->getOption('purge')) {
            $this->updater->purge();
        }

        $this->updateAll(
            intval($input->getOption('interval')),
            intval($input->getOption('limit')),
            intval($input->getOption('breathe'))
        );

        return Command::SUCCESS;
    }

    private function updateAll(
        int $interval,
        int $limit,
        int $breathe
    ): void {
        $maxDate = (new DateTime())->modify("-$interval hours");

        $limit *= 1000;
        $count = $sum = $avg = 0;

        while ($limit > $sum + $avg * 2) {
            if (null === $product = $this->repository->findNextStatUpdate($maxDate)) {
                break;
            }

            $time = $this->updater->update($product);

            if (0 < $breathe) {
                usleep($breathe * 1000);
                $sum += $breathe;
            }

            $count++;
            $sum += $time;
            $avg = $sum / $count;
        }

        if ($this->debug) {
            $this->output->writeln("Updated <comment>$count</comment> products in <comment>{$sum}ms</comment>.");
            $this->output->writeln('');
        }
    }
}

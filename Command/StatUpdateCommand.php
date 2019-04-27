<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\ProductBundle\Service\Stat\StatUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StatUpdateCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatUpdateCommand extends Command
{
    /**
     * @var StatUpdater
     */
    private $updater;

    /**
     * Constructor.
     *
     * @param StatUpdater $updater
     */
    public function __construct(StatUpdater $updater)
    {
        $this->updater = $updater;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:product:stat:update')
            ->setDescription('Updates the products stats')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Whether to force update')
            ->addOption('purge', null, InputOption::VALUE_NONE, 'Whether to purge stats first')
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'The interval between 2 updates for a given product in hours', 6)
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'The maximum execution time in seconds', 120)
            ->addOption('breathe', null, InputOption::VALUE_REQUIRED, 'Delay between each product update in milliseconds', 0);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $debug = !$input->getOption('no-debug');
        $force = (bool)$input->getOption('force');

        $interval = intval($input->getOption('interval'));
        $limit = intval($input->getOption('limit'));
        $breathe = intval($input->getOption('breathe'));

        $this->updater->setOutput($output);
        $this->updater->setDebug($debug);
        $this->updater->setForce($force);

        if ($input->getOption('purge')) {
            $this->updater->purge();
        }

        $this->updater->setMaxUpdateDate((new \DateTime())->modify("-$interval hours"));

        $limit *= 1000;
        $count = $sum = $avg = 0;

        while ($limit > $sum + $avg * 2) {
            if (null === $time = $this->updater->updateNextProduct()) {
                break;
            }

            if (0 < $breathe) {
                usleep($breathe * 1000);
                $sum += $breathe;
            }

            $count++;
            $sum += $time;
            $avg = $sum / $count;
        }

        if ($debug) {
            $output->writeln("Updated <comment>$count</comment> products in <comment>{$sum}ms</comment>.");
            $output->writeln("");
        }
    }
}

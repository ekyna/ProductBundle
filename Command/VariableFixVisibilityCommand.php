<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\ProductBundle\Service\Updater\VariableUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class VariableFixVisibilityCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariableFixVisibilityCommand extends AbstractVariableCommand
{
    protected static $defaultName = 'ekyna:product:variable:fix_visibility';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Fixes the variables visibility');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $variables = $this->getVariables($input);

        $updater = new VariableUpdater($this->calculator);

        $count = 0;
        foreach ($variables as $variable) {
            $name = $variable->getTitle();
            $output->write(sprintf('<comment>%s</comment> %s ',
                $name,
                str_pad('.', 80 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));
            if (!$updater->updateVisibility($variable)) {
                $output->writeln('<comment>passed</comment>');
                continue;
            }

            $output->writeln('<info>updated</info>');
            $this->manager->persist($variable);
            $count++;

            if ($count % 20 == 0) {
                $this->manager->flush();
            }
        }

        if ($count % 20 != 0) {
            $this->manager->flush();
        }

        return Command::SUCCESS;
    }
}

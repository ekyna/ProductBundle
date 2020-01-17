<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Updater\VariableUpdater;
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

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this->setDescription('Fixes the variables visibility');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $variables = $this->getVariables($input);

        $updater = new VariableUpdater($this->calculator);

        $count = 0;
        /** @var ProductInterface $variable */
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
    }
}

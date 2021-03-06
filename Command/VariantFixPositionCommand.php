<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Updater\VariableUpdater;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class VariantFixPositionCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantFixPositionCommand extends AbstractVariableCommand
{
    protected static $defaultName = 'ekyna:product:variant:fix_position';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this->setDescription('Fixes the variants positions');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $updater = new VariableUpdater($this->calculator);

        $variables = $this->getVariables($input);

        $count = 0;
        /** @var ProductInterface $variable */
        foreach ($variables as $variable) {
            $name = $variable->getTitle();
            $output->write(sprintf('<comment>%s</comment> %s ',
                $name,
                str_pad('.', 80 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));
            if (!$updater->indexVariantsPositions($variable)) {
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

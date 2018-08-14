<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Updater\VariableUpdater;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FixVariableVisibilityCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FixVariableVisibilityCommand extends ContainerAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:product:variable:fix_visibility')
            ->setDescription('Fixes the variables visibility')
            ->addArgument('variableId', InputArgument::OPTIONAL, 'The variable product identifier to fix the visibility of.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->getContainer()->get('ekyna_product.product.repository');

        if (0 < $variableId = intval($input->getArgument('variableId'))) {
            $variable = $repository->findOneBy([
                'id'   => $variableId,
                'type' => ProductTypes::TYPE_VARIABLE,
            ]);
            if (null === $variable) {
                throw new \InvalidArgumentException("Variable product with id $variableId not found.");
            }
            $variables = [$variable];
        } else {
            $variables = $repository->findBy(['type' => ProductTypes::TYPE_VARIABLE]);
        }

        $manager = $this->getContainer()->get('ekyna_product.product.manager');
        $calculator = $this->getContainer()->get('ekyna_product.pricing.calculator');
        $updater = new VariableUpdater($calculator);

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
            $manager->persist($variable);
            $count++;

            if ($count % 20 == 0) {
                $manager->flush();
            }
        }

        if ($count % 20 != 0) {
            $manager->flush();
        }
    }
}

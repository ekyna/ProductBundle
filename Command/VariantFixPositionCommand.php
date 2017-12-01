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
 * Class VariantFixPositionCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantFixPositionCommand extends ContainerAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:product:variant:fix__position')
            ->setDescription('Fixes the variants positions')
            ->addArgument('variableId', InputArgument::OPTIONAL, 'The variable product identifier which variants will be fixed.');
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
        $updater = new VariableUpdater();

        $count = 0;
        /** @var ProductInterface $variable */
        foreach ($variables as $variable) {
            $name = $variable->getTitle();
            $output->write(sprintf('<comment>%s</comment> %s ',
                $name,
                str_pad('.', 64 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));
            if (!$updater->indexVariantsPositions($variable)) {
                $output->write('<comment>passed</comment>');
                continue;
            }

            $output->write('<info>updated</info>');
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

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\ProductBundle\Exception\LogicException;
use Ekyna\Bundle\ProductBundle\Repository\InventoryRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Inventory\InventoryApplier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use function sprintf;

/**
 * Class InventoryApplyCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InventoryApplyCommand extends Command
{
    protected static $defaultName = 'ekyna:product:inventory:apply';

    public function __construct(
        private readonly InventoryRepositoryInterface $repository,
        private readonly InventoryApplier             $applier,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inventory = $this->repository->findOneOpened();

        if (null === $inventory) {
            $output->writeln('No opened inventory.');

            return Command::SUCCESS;
        }

        $helper = $this->getHelper('question');

        $confirm = new ConfirmationQuestion(sprintf(
            'Are you sure you want to apply and close %s inventory ?',
            $inventory->getCreatedAt()->format('Y-m-d')
        ));

        if (!$helper->ask($input, $output, $confirm)) {
            return Command::SUCCESS;
        }

        try {
            $this->applier->apply($inventory);
        } catch (LogicException $exception) {
            $output->writeln($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

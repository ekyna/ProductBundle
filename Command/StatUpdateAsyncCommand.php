<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\ProductBundle\Message\UpdateProductStat;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class StatUpdateAsyncCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatUpdateAsyncCommand extends Command
{
    protected static $defaultName        = 'ekyna:product:stat:update_async';
    protected static $defaultDescription = 'Updates the products stats through messenger bus';

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly MessageBusInterface        $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Whether to force update');
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'The product ID to start with', null);
        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, 'The number of products to update', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $qb = $this
            ->productRepository
            ->createQueryBuilder('p')
            ->select('p.id');

        $id = (int)$input->getOption('id');
        if (0 < $id) {
            $qb->andWhere($qb->expr()->gte('p.id', $id));
        }

        $limit = (int)$input->getOption('limit');
        if (0 < $limit) {
            $qb->setMaxResults($limit);
        }

        $results = $qb
            ->getQuery()
            ->getScalarResult();

        $force = (bool)$input->getOption('force');

        foreach ($results as $result) {
            $this->messageBus->dispatch(new UpdateProductStat((int)$result['id'], $force));
        }

        return Command::SUCCESS;
    }
}

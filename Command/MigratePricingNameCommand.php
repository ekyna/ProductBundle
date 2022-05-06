<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\ProductBundle\Service\Migration\PricingNameMigrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigratePricingNameCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MigratePricingNameCommand extends Command
{
    protected static $defaultName = 'ekyna:product:migrate:pricing_name';

    private PricingNameMigrator $migrator;

    public function __construct(PricingNameMigrator $migrator)
    {
        parent::__construct();

        $this->migrator = $migrator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->migrator->migrate();

        return Command::SUCCESS;
    }
}

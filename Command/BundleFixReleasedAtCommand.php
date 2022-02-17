<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Bundle\ProductBundle\Service\Updater\BundleUpdater;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function intval;
use function sprintf;
use function str_pad;

use const STR_PAD_LEFT;

/**
 * Class BundleFixReleasedAtCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BundleFixReleasedAtCommand extends Command
{
    protected static $defaultName = 'ekyna:product:bundle:fix_released_at';

    private ProductRepositoryInterface $repository;
    private EntityManagerInterface     $manager;
    private PriceCalculator $priceCalculator;

    public function __construct(
        ProductRepositoryInterface $repository,
        EntityManagerInterface     $manager,
        PriceCalculator            $priceCalculator
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->manager = $manager;
        $this->priceCalculator = $priceCalculator;
    }

    protected function configure(): void
    {
        $this->addArgument('bundleId', InputArgument::REQUIRED, 'The bundle product identifier.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bundleId = intval($input->getArgument('bundleId'));

        $bundle = $this->repository->findOneBy([
            'id'   => $bundleId,
            'type' => ProductTypes::TYPE_BUNDLE,
        ]);

        if (!$bundle instanceof ProductInterface) {
            throw new InvalidArgumentException('Bundle product not found.');
        }

        $name = $bundle->getTitle();
        $output->write(sprintf('<comment>%s</comment> %s ',
            $name,
            str_pad('.', 80 - mb_strlen($name), '.', STR_PAD_LEFT)
        ));

        $updater = new BundleUpdater($this->priceCalculator);

        if (!$updater->updateReleasedAt($bundle)) {
            $output->writeln('<comment>passed</comment>');
            return Command::SUCCESS;
        }

        $output->writeln('<info>updated</info>');

        $this->manager->persist($bundle);
        $this->manager->flush();

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferUpdater;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class OfferUpdateCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferUpdateCommand extends Command
{
    protected static $defaultName = 'ekyna:product:offer:update';

    private ProductRepositoryInterface $repository;
    private OfferUpdater               $offerUpdater;
    private PriceUpdater               $priceUpdater;
    private EntityManagerInterface     $manager;
    private int                        $timeout;

    public function __construct(
        ProductRepositoryInterface $repository,
        OfferUpdater               $offerUpdater,
        PriceUpdater               $priceUpdater,
        EntityManagerInterface     $manager
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->offerUpdater = $offerUpdater;
        $this->priceUpdater = $priceUpdater;
        $this->manager = $manager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Updates the product(s) offers')
            ->addArgument('id', InputArgument::OPTIONAL, 'The product identifier to update the offers of.')
            ->addOption('max_execution_time', 't', InputOption::VALUE_OPTIONAL, 'Max execution time in seconds', 59);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->timeout = time() + $input->getOption('max_execution_time');

        if (0 < $id = intval($input->getArgument('id'))) {
            /** @var ProductInterface $product */
            $product = $this->repository->find($id);
            if (null === $product) {
                throw new InvalidArgumentException("Product with id $id not found.");
            }

            $confirm = new ConfirmationQuestion('Do you want to continue ?');
            $helper = $this->getHelper('question');
            if (!$helper->ask($input, $output, $confirm)) {
                $output->writeln('Abort by user.');

                return Command::SUCCESS;
            }

            $this->updateProduct($output, $product);

            return Command::SUCCESS;
        }

        $confirm = new ConfirmationQuestion('Do you want to continue ?');
        $helper = $this->getHelper('question');
        if (!$helper->ask($input, $output, $confirm)) {
            $output->writeln('Abort by user.');

            return Command::SUCCESS;
        }

        $this->updateAll($output);

        return Command::SUCCESS;
    }

    /**
     * Update the given product's offers and prices.
     */
    protected function updateProduct(OutputInterface $output, ProductInterface $product): void
    {
        $output->writeln('Updating offers:');
        $this->updateOffers($product, $output);
        $output->writeln('Updating prices:');
        $this->updatePrices($product, $output);
    }

    /**
     * Update all the products offers and prices.
     */
    protected function updateAll(OutputInterface $output): void
    {
        $output->writeln('Updating offers:');
        $output->writeln('');

        $types = ProductTypes::getTypes();

        foreach ($types as $type) {
            while (null !== $product = $this->repository->findOneByPendingOffers($type)) {
                $this->updateOffers($product, $output);

                $this->manager->clear();

                if (time() > $this->timeout) {
                    return;
                }
            }
        }

        $output->writeln('');
        $output->writeln('Updating prices:');
        $output->writeln('');

        foreach ($types as $type) {
            while (null !== $product = $this->repository->findOneByPendingPrices($type)) {
                $this->updatePrices($product, $output);

                $this->manager->clear();

                if (time() > $this->timeout) {
                    return;
                }
            }
        }
    }

    /**
     * Updates the product offers.
     */
    protected function updateOffers(ProductInterface $product, OutputInterface $output): void
    {
        $this->productName($product, $output);

        if ($this->offerUpdater->updateProduct($product)) {
            $output->writeln('<info>updated</info>');
        } else {
            $output->writeln('<comment>up to date</comment>');
        }

        $this->manager->flush();
    }

    /**
     * Updates the product prices.
     */
    protected function updatePrices(ProductInterface $product, OutputInterface $output): void
    {
        $this->productName($product, $output);

        if ($this->priceUpdater->updateProduct($product)) {
            $output->writeln('<info>updated</info>');
        } else {
            $output->writeln('<comment>up to date</comment>');
        }

        $this->manager->flush();
    }

    /**
     * Outputs the product name.
     */
    protected function productName(ProductInterface $product, OutputInterface $output): void
    {
        $name = sprintf('[%d] (%s) %s', $product->getId(), $product->getType(), $product->getFullDesignation());

        if (77 < mb_strlen($name)) {
            $name = mb_substr($name, 0, 77);
        }

        $output->write(sprintf('<comment>%s</comment> %s ',
            $name,
            str_pad('.', 80 - mb_strlen($name), '.', STR_PAD_LEFT)
        ));
    }
}

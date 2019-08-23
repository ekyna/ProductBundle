<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepository;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferUpdater;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceUpdater;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
class OfferUpdateCommand extends ContainerAwareCommand
{
    /**
     * @var ProductRepository
     */
    private $repository;

    /**
     * @var OfferUpdater
     */
    private $offerUpdater;

    /**
     * @var PriceUpdater
     */
    private $priceUpdater;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var int
     */
    private $timeout;


    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:product:offer:update')
            ->setDescription('Updates the product(s) offers')
            ->addArgument('id', InputArgument::OPTIONAL, 'The product identifier to update the offers of.')
            ->addOption('max_execution_time', 't', InputOption::VALUE_OPTIONAL, 'Max execution time in seconds', 59);
    }

    /**
     * @inheritDoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->timeout = time() + $input->getOption('max_execution_time');

        $container = $this->getContainer();

        $this->repository = $container->get('ekyna_product.product.repository');
        $this->offerUpdater = $container->get('ekyna_product.offer.updater');
        $this->priceUpdater = $container->get('ekyna_product.price.updater');
        $this->manager = $container->get('doctrine.orm.default_entity_manager');

        // TODO Disable some loggers
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (0 < $id = intval($input->getArgument('id'))) {
            /** @var ProductInterface $product */
            $product = $this->repository->find($id);
            if (null === $product) {
                throw new InvalidArgumentException("Product with id $id not found.");
            }

            $confirm = new ConfirmationQuestion("Do you want to continue ?");
            $helper = $this->getHelper('question');
            if (!$helper->ask($input, $output, $confirm)) {
                $output->writeln('Abort by user.');

                return;
            }

            $this->updateProduct($output, $product);

            return;
        }

        $confirm = new ConfirmationQuestion("Do you want to continue ?");
        $helper = $this->getHelper('question');
        if (!$helper->ask($input, $output, $confirm)) {
            $output->writeln('Abort by user.');

            return;
        }

        $this->updateAll($output);
    }

    /**
     * Update the given product's offers and prices.
     *
     * @param OutputInterface  $output
     * @param ProductInterface $product
     */
    protected function updateProduct(OutputInterface $output, ProductInterface $product)
    {
        $output->writeln('Updating offers:');
        $this->updateOffers($product, $output);
        $output->writeln('Updating prices:');
        $this->updatePrices($product, $output);
    }

    /**
     * Update all the products offers and prices.
     *
     * @param OutputInterface $output
     */
    protected function updateAll(OutputInterface $output)
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
     *
     * @param ProductInterface $product
     * @param OutputInterface  $output
     */
    protected function updateOffers(ProductInterface $product, OutputInterface $output)
    {
        $this->productName($product, $output);

        if ($this->offerUpdater->updateByProduct($product)) {
            $output->writeln('<info>updated</info>');
        } else {
            $output->writeln('<comment>up to date</comment>');
        }

        $this->manager->flush();
    }

    /**
     * Updates the product prices.
     *
     * @param ProductInterface $product
     * @param OutputInterface  $output
     */
    protected function updatePrices(ProductInterface $product, OutputInterface $output)
    {
        $this->productName($product, $output);

        if ($this->priceUpdater->updateByProduct($product)) {
            $output->writeln('<info>updated</info>');
        } else {
            $output->writeln('<comment>up to date</comment>');
        }

        $this->manager->flush();
    }

    /**
     * Outputs the product name.
     *
     * @param ProductInterface $product
     * @param OutputInterface  $output
     */
    protected function productName(ProductInterface $product, OutputInterface $output)
    {
        $name = sprintf('[%d] (%s) %s', $product->getId(), $product->getType(), $product->getFullDesignation());

        if (77 < $tmp = mb_strlen($name)) {
            $name = mb_substr($name, 0, 77);
        }

        $output->write(sprintf('<comment>%s</comment> %s ',
            $name,
            str_pad('.', 80 - mb_strlen($name), '.', STR_PAD_LEFT)
        ));
    }
}

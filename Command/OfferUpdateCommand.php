<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepository;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferUpdater;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
    private $updater;

    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:product:offer:update')
            ->setDescription('Updates the product(s) offers')
            ->addArgument('productId', InputArgument::OPTIONAL, 'The product identifier to update the offers of.')
            ->addOption('max_execution_time', 't', InputOption::VALUE_OPTIONAL, 'Max execution time in seconds', 59);
    }

    /**
     * @inheritDoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $this->repository = $container->get('ekyna_product.product.repository');
        $this->updater = $container->get('ekyna_product.offer.updater');
        $this->manager = $container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($productId = intval($input->getArgument('productId'))) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
            $product = $this->repository->findOneBy(['id' => $productId]);
            if (null === $product) {
                throw new \InvalidArgumentException("Product with id $productId not found.");
            }

            $this->update($product, $output);

            return;
        }

        $max = time() + $input->getOption('max_execution_time');

        while (null !== $product = $this->repository->findOneByPendingOffers()) {
            $this->update($product, $output);

            if (time() > $max) {
                break;
            }
        }
    }

    /**
     * Updates the product offers.
     *
     * @param ProductInterface $product
     * @param OutputInterface  $output
     */
    protected function update(ProductInterface $product, OutputInterface $output)
    {
        $name = "[{$product->getId()}] " . $product->getFullDesignation();
        $output->write(sprintf('<comment>%s</comment> %s ',
            $name,
            str_pad('.', 80 - mb_strlen($name), '.', STR_PAD_LEFT)
        ));

        if ($this->updater->updateByProduct($product)) {
            $output->writeln('<info>updated</info>');
        } else {
            $output->writeln('<comment>up to date</comment>');
        }

        $this->manager->flush();
        $this->manager->clear();
    }
}

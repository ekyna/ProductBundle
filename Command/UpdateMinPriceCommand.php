<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateMinPriceCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UpdateMinPriceCommand extends Command
{
    /**
     * @var ProductRepositoryInterface
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var PriceCalculator
     */
    private $calculator;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $repository
     * @param EntityManagerInterface     $manager
     * @param PriceCalculator            $calculator
     */
    public function __construct(
        ProductRepositoryInterface $repository,
        EntityManagerInterface $manager,
        PriceCalculator $calculator
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->manager = $manager;
        $this->calculator = $calculator;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:product:update:min_price')
            ->setDescription('Updates the product(s) min price')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'The product identifier to update the price of.')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'The products type to update the price of.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $id = intval($input->getOption('id'));
        $type = $input->getOption('type');

        if ($id && $type) {
            $output->writeln("<error>You must provider either 'id' or 'type' option but not both.</error>");

            return;
        }

        // By id
        if (0 < $id) {
            /** @var ProductInterface $product */
            $product = $this->repository->find($id);
            if (null === $product) {
                throw new \InvalidArgumentException("Product with id $id not found.");
            }

            if ($this->doUpdate($product, $output)) {
                $this->manager->persist($product);
                $this->manager->flush();
            }

            return;
        }

        $types = $type ? [$type] : ProductTypes::getTypes();

        // By type(s)
        foreach ($types as $type) {
            $title = strtoupper($type);
            $output->writeln("");
            $output->writeln(str_pad(" $title ", 80, '-', STR_PAD_BOTH));
            $output->writeln("");

            $offset = 0;
            do {
                $products = (array)$this->repository->findBy([
                    'type' => $type,
                ], null, 20, $offset * 20)->getIterator();

                if (empty($products)) {
                    continue 2;
                }

                $doFlush = false;
                /** @var ProductInterface $product */
                foreach ($products as $product) {
                    if ($this->doUpdate($product, $output)) {
                        $this->manager->persist($product);
                        $doFlush = true;
                    }
                }

                if ($doFlush) {
                    $this->manager->flush();
                }
                $this->manager->clear();

                $offset++;
            } while (!empty($products));
        }
    }

    /**
     * Performs the minimum price update.
     *
     * @param ProductInterface $product
     * @param OutputInterface  $output
     *
     * @return bool
     */
    private function doUpdate(ProductInterface $product, OutputInterface $output)
    {
        $name = sprintf('[%d] %s', $product->getId(), $product->getFullDesignation());

        $output->write(sprintf('<comment>%s</comment> %s ',
            $name,
            str_pad('.', 80 - mb_strlen($name), '.', STR_PAD_LEFT)
        ));

        $price = $this->calculator->calculateMinPrice($product);
        if (is_null($product->getMinPrice()) || 0 !== bccomp($product->getMinPrice(), $price, 5)) {
            $product->setMinPrice($price);

            $output->writeln('<info>updated</info>');

            return true;
        }

        $output->writeln('<comment>passed</comment>');

        return false;
    }
}

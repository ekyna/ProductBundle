<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StockUpdateCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUpdateCommand extends AbstractStockCommand
{
    protected static $defaultName = 'ekyna:product:stock:update';

    /**
     * @var ResourceOperatorInterface
     */
    private $operator;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(ProductRepositoryInterface $repository, ResourceOperatorInterface $operator)
    {
        parent::__construct($repository);

        $this->operator = $operator;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription("Updates the product stock.")
            ->addArgument('id', InputArgument::REQUIRED, "The product's id to update.");
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $product = $this->findProduct($input->getArgument('id'));

        $product->setInStock(0);

        $this->operator->update($product);

        $this->stockTable($output, [$product]);
    }
}

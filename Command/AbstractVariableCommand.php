<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class AbstractVariableCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractVariableCommand extends Command
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $repository;

    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @var PriceCalculator
     */
    protected $calculator;


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
        $this->manager    = $manager;
        $this->calculator = $calculator;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->addArgument('variableId', InputArgument::OPTIONAL, 'The variable product identifier.');
    }

    /**
     * Returns the selected variables.
     *
     * @param InputInterface $input
     *
     * @return Model\ProductInterface[]
     */
    protected function getVariables(InputInterface $input): array
    {
        if (0 < $variableId = intval($input->getArgument('variableId'))) {
            $variable = $this->repository->findOneBy([
                'id'   => $variableId,
                'type' => Model\ProductTypes::TYPE_VARIABLE,
            ]);

            if (null === $variable) {
                throw new \InvalidArgumentException("Variable product with id $variableId not found.");
            }

            return [$variable];
        }

        return $this->repository->findBy(['type' => Model\ProductTypes::TYPE_VARIABLE]);
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class StockUpdateCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUpdateCommand extends AbstractStockCommand
{
    protected static $defaultName = 'ekyna:product:stock:update';

    private StockSubjectUpdaterInterface $updater;
    private EntityManagerInterface       $manager;

    private ?Query $query = null;
    private int    $id;

    public function __construct(
        ProductRepositoryInterface   $repository,
        StockSubjectUpdaterInterface $updater,
        EntityManagerInterface       $manager
    ) {
        parent::__construct($repository);

        $this->updater = $updater;
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Updates the product stock.')
            ->addArgument('id', InputArgument::OPTIONAL, "The product's id to update.");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->persist = $input->getOption('persist');

        if (!$this->persist) {
            $output->writeln('<comment>Dry-run. Use --persist if you want to.</comment>');
        }

        if (0 < $id = (int)$input->getArgument('id')) {
            if (!$product = $this->findProduct($id)) {
                $output->writeln('<error>No product found</error>');

                return Command::FAILURE;
            }

            if (!$this->doUpdate($output, $product)) {
                $output->writeln('<info>Product is up-to-date.</info>');
            }

            return Command::SUCCESS;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Update all products ?', false);

        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        $this->updateAll($output);

        return Command::SUCCESS;
    }

    private function getQuery(): Query
    {
        if ($this->query) {
            return $this->query;
        }

        if (!$this->repository instanceof EntityRepository) {
            throw new UnexpectedTypeException($this->repository, EntityRepository::class);
        }

        $qb = $this->repository->createQueryBuilder('p');

        $query = $qb
            ->select('p')
            ->andWhere($qb->expr()->gt('p.id', ':id'))
            ->andWhere($qb->expr()->in('p.type', [ProductTypes::TYPE_SIMPLE, ProductTypes::TYPE_VARIANT]))
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery();

        return $this->query = $query;
    }

    private function updateAll(OutputInterface $output): void
    {
        // TODO Symfony 4.1+ (need Table::appendRow)
        /*$table = new Table($output);
        $table->setHeaders(['ID', 'SKU', 'Mode', 'State', 'In', 'Available', 'Virtual', 'EDA']);
        $table->render();*/

        $this->id = 0;
        $total = $count = 0;

        while ($product = $this->findNext()) {
            $total++;

            if ($this->doUpdate($output, $product)) {
                $count++;
            }

            // TODO Symfony 4.1+ (need Table::appendRow)
            //$post = $this->extractData($product);
            //$table->appendRow($this->buildRow($pre, $post));
        }

        $output->writeln("Updated $count / $total product(s).");
    }

    private function findNext(): ?ProductInterface
    {
        /** @var ProductInterface|null $product */
        $product = $this
            ->getQuery()
            ->setParameter('id', $this->id)
            ->getOneOrNullResult();

        if ($product) {
            $this->id = $product->getId();
        }

        return $product;
    }

    private function doUpdate(OutputInterface $output, ProductInterface $product): bool
    {
        $pre = $this->extractData($product);

        $this->updater->update($product);

        if ($this->persist) {
            $this->manager->persist($product);
            $this->manager->flush();
            $this->manager->clear();
        }

        return $this->showDiff($output, $product, $pre);
    }

    private function showDiff(OutputInterface $output, ProductInterface $product, array $pre): bool
    {
        $post = $this->extractData($product);

        if ($post == $pre) {
            return false;
        }

        $output->writeln(sprintf(
            '[%s] %s - %s :',
            $product->getId(),
            $product->getReference(),
            $product->getDesignation()
        ));

        foreach ($post as $index => $data) {
            if ($data == $pre[$index]) {
                continue;
            }

            $output->writeln(sprintf('  - %s : %s -> <comment>%s</comment>', $index, $pre[$index], $data));
        }

        $output->writeln('');

        return true;
    }

    /* TODO Symfony 4.1+ (need Table::appendRow)
    private function buildRow(array $pre, array $post): array
    {
        $row = [];
        foreach ($post as $index => $data) {
            if ($data != $pre[$index]) {
                $data = "<comment>$data</comment>";
            }
            $row[] = $data;
        }

        return $row;
    }*/

    private function extractData(ProductInterface $product): array
    {
        if ($eda = $product->getEstimatedDateOfArrival()) {
            $eda = $eda->format('d-m-Y');
        }

        return [
            // TODO Symfony 4.1+ (need Table::appendRow)
            //'id'                => $product->getId(),
            //'reference'         => $product->getReference(),
            'Stock mode'      => $product->getStockMode(),
            'stock state'     => $product->getStockState(),
            'In stock'        => $product->getInStock()->toFixed(3),
            'Available stock' => $product->getAvailableStock()->toFixed(3),
            'Virtual stock'   => $product->getVirtualStock()->toFixed(3),
            'EDA'             => $eda,
        ];
    }
}

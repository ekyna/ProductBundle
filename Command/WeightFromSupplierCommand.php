<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Ekyna\Bundle\ProductBundle\Entity\Product;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WeightFromSupplierCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WeightFromSupplierCommand extends Command
{
    protected static $defaultName = 'ekyna:product:weight_from_supplier';

    /**
     * @var EntityRepository
     */
    private $productRepository;

    /**
     * @var EntityRepository
     */
    private $supplierProductRepository;

    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * Constructor.
     *
     * @param EntityRepository       $productRepository
     * @param EntityRepository       $supplierProductRepository
     * @param EntityManagerInterface $manager
     */
    public function __construct(
        EntityRepository $productRepository,
        EntityRepository $supplierProductRepository,
        EntityManagerInterface $manager
    ) {
        parent::__construct();

        $this->productRepository         = $productRepository;
        $this->supplierProductRepository = $supplierProductRepository;
        $this->manager                   = $manager;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Updates the products weights from the supplier references')
            ->addArgument('productId', InputArgument::OPTIONAL, 'The product identifier to update the weight.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $qb = $this->productRepository->createQueryBuilder('p');
        $qb
            ->andWhere($qb->expr()->in('p.type', ':types'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('p.weight', ':weight'),
                $qb->expr()->isNull('p.weight')
            ));

        $parameters = [
            'types'  => [ProductTypes::TYPE_SIMPLE, ProductTypes::TYPE_VARIANT],
            'weight' => 0,
        ];
        if (0 < $productId = intval($input->getArgument('productId'))) {
            $qb->andWhere($qb->expr()->eq('p.id', ':product_id'));
            $parameters['product_id'] = $productId;
        }

        $products = $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();

        if (empty($products)) {
            $output->writeln('Not product with empty weight found.');

            return;
        }

        $referenceQuery = $this
            ->supplierProductRepository
            ->createQueryBuilder('r')
            ->select('r.weight')
            ->andWhere($qb->expr()->eq('r.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('r.subjectIdentity.identifier', ':identifier'))
            ->andWhere($qb->expr()->gt('r.weight', ':weight'))
            ->addOrderBy('r.weight', 'ASC')
            ->getQuery()
            ->setMaxResults(1)
            ->setParameters([
                'provider' => Product::getProviderName(),
                'weight'   => 0,
            ]);

        $count  = 0;
        $nCount = 0;

        /** @var ProductInterface $product */
        foreach ($products as $product) {
            $name = $product->getReference();
            $output->write(sprintf('<comment>%s</comment> %s ',
                $name,
                str_pad('.', 32 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            $weight = (float)$referenceQuery
                ->setParameter('identifier', $product->getIdentifier())
                ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

            if (0 == $weight) {
                $output->writeln('<comment>not found</comment>');
                $nCount++;

                continue;
            }

            $this->manager->persist($product->setPackageWeight($weight));

            $output->writeln('<info>' . round($weight, 3) . '</info>');

            $count++;
            if ($count % 20 == 0) {
                $this->manager->flush();
            }
        }

        if ($count % 20 != 0) {
            $this->manager->flush();
        }

        $output->writeln("");
        $output->writeln("$count product(s) updated.");
        $output->writeln("$nCount product(s) skipped (weight not found).");
    }
}

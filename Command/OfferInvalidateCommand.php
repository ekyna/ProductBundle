<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Repository\SpecialOfferRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OfferInvalidateCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferInvalidateCommand extends Command
{
    protected static $defaultName = 'ekyna:product:offer:invalidate';

    /**
     * @var SpecialOfferRepositoryInterface
     */
    private $repository;

    /**
     * @var OfferInvalidator
     */
    private $invalidator;

    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * Constructor.
     *
     * @param SpecialOfferRepositoryInterface $repository
     * @param OfferInvalidator                $invalidator
     * @param EntityManagerInterface          $manager
     */
    public function __construct(
        SpecialOfferRepositoryInterface $repository,
        OfferInvalidator $invalidator,
        EntityManagerInterface $manager
    ) {
        parent::__construct();

        $this->repository  = $repository;
        $this->invalidator = $invalidator;
        $this->manager     = $manager;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setDescription('Invalidates the obsolete offers');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $offers = $this->repository->findStartingTodayOrEndingYesterday();

        if (empty($offers)) {
            return;
        }

        foreach ($offers as $offer) {
            $this->invalidator->invalidateSpecialOffer($offer);
        }

        $this->invalidator->flush($this->manager);
    }
}

<?php

declare(strict_types=1);

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

    public function __construct(
        private readonly SpecialOfferRepositoryInterface $repository,
        private readonly OfferInvalidator                $invalidator,
        private readonly EntityManagerInterface          $manager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Invalidates the obsolete offers');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $offers = $this->repository->findStartingTodayOrEndingYesterday();

        if (empty($offers)) {
            return Command::SUCCESS;
        }

        foreach ($offers as $offer) {
            $this->invalidator->invalidateSpecialOffer($offer);
        }

        $this->invalidator->flush();

        return Command::SUCCESS;
    }
}

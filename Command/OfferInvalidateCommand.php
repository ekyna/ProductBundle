<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OfferInvalidateCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferInvalidateCommand extends ContainerAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:product:offer:invalidate')
            ->setDescription('Invalidates the obsolete offers');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $offers = $container
            ->get('ekyna_product.special_offer.repository')
            ->findStartingTodayOrEndingYesterday();

        if (empty($offers)) {
            return;
        }

        $invalidator = $container->get('ekyna_product.offer.invalidator');

        foreach ($offers as $offer) {
            $invalidator->invalidateSpecialOffer($offer);
        }

        $invalidator->flush($container->get('doctrine.orm.default_entity_manager'));
    }
}

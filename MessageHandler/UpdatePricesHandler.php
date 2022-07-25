<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Message\UpdatePrices;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceUpdater;

/**
 * Class UpdatePricesHandler
 * @package Ekyna\Bundle\ProductBundle\MessageHandler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UpdatePricesHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
        private readonly PriceUpdater $updater,
        private readonly EntityManagerInterface $manager
    ) {
    }

    public function __invoke(UpdatePrices $message): void
    {
        $product = $this->repository->find($message->getProductId());

        if (null === $product) {
            return;
        }

        $this->updater->updateProduct($product);

        $this->manager->flush();
        $this->manager->clear();
    }
}

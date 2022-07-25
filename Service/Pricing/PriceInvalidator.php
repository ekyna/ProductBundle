<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Message\UpdatePrices;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Resource\Message\MessageQueue;

/**
 * Class PriceInvalidator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceInvalidator extends AbstractInvalidator
{
    public function __construct(
        EntityManagerInterface     $entityManager,
        ProductRepositoryInterface $productRepository,
        MessageQueue               $messageQueue,
        string                     $offerClass
    ) {
        parent::__construct($entityManager, $productRepository, $messageQueue, $offerClass, 'pendingPrices');
    }

    protected function createMessage(array|int $productId): object
    {
        return new UpdatePrices($productId);
    }
}

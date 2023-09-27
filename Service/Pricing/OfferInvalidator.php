<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Message\UpdateOffers;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Resource\Message\MessageQueue;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Class OfferInvalidator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferInvalidator extends AbstractInvalidator
{
    public function __construct(
        EntityManagerInterface     $entityManager,
        ProductRepositoryInterface $productRepository,
        MessageQueue               $messageQueue,
        string                     $offerClass
    ) {
        parent::__construct($entityManager, $productRepository, $messageQueue, $offerClass, 'pendingOffers');
    }

    protected function createMessage(int $productId): object
    {
        return (new Envelope(new UpdateOffers($productId)))->with(new DelayStamp(1000));
    }
}

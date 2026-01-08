<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\MessageHandler;

use Ekyna\Bundle\ProductBundle\Message\UpdateProductStat;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Stat\StatUpdater;
use Exception;
use Symfony\Component\Messenger\Handler\Acknowledger;
use Symfony\Component\Messenger\Handler\BatchHandlerInterface;
use Symfony\Component\Messenger\Handler\BatchHandlerTrait;
use Throwable;

/**
 * Class UpdateProductStatHandler
 * @package Ekyna\Bundle\ProductBundle\MessageHandler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UpdateProductStatHandler implements BatchHandlerInterface
{
    use BatchHandlerTrait;

    public function __construct(
        private readonly ProductRepositoryInterface $repository,
        private readonly StatUpdater                $updater,
    ) {
    }

    public function __invoke(UpdateProductStat $message, ?Acknowledger $ack = null): int
    {
        return $this->handle($message, $ack);
    }

    private function process(array $jobs): void
    {
        $this->updater->setForce(true); // TODO from message property ?

        /**
         * @var UpdateProductStat $message
         * @var Acknowledger $ack
         */
        foreach ($jobs as [$message, $ack]) {
            try {
                if (null === $product = $this->repository->find($message->getProductId())) {
                    throw new Exception('Product not found');
                }

                $this->updater->update($product);

                // Acknowledge the processing of the message
                $ack->ack(1);
            } catch (Throwable $e) {
                $ack->nack($e);
            }
        }
    }

    private function shouldFlush(): bool
    {
        return 10 <= \count($this->jobs);
    }
}

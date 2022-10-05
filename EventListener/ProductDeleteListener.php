<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Message\ProductDeletion;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\CatalogRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Message\MessageQueueInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ProductDeleteListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductDeleteListener
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CatalogRepositoryInterface $catalogRepository,
        private readonly MessageQueueInterface      $messageQueue,
        private readonly ResourceHelper             $resourceHelper,
        private readonly TranslatorInterface        $translator,
    ) {
    }

    public function onPreDelete(ResourceEventInterface $event): void
    {
        $product = $this->getProductFromEvent($event);

        // BundleChoice
        $parents = $this->productRepository->findParentsByBundled($product, limit: 1);
        if (!empty($parents)) {
            $event->addMessage($this->createMessage($parents[0], 'bundle_slot.label.singular'));

            return;
        }

        // Component
        $parents = $this->productRepository->findParentsByComponent($product, limit: 1);
        if (!empty($parents)) {
            $event->addMessage($this->createMessage($parents[0], 'component.label.singular'));

            return;
        }

        // Option
        $parents = $this->productRepository->findParentsByOptionProduct($product, limit: 1);
        if (!empty($parents)) {
            $event->addMessage($this->createMessage($parents[0], 'option.label.singular'));

            return;
        }

        // Catalogs
        $catalogs = $this->catalogRepository->findByProduct($product, 1);
        if (!empty($catalogs)) {
            $catalog = $catalogs[0];
            $message = ResourceMessage::create('product.message.relation_prevents_deletion', ResourceMessage::TYPE_ERROR)
                ->setParameters([
                    '{url}'   => $this->resourceHelper->generateResourcePath($catalog, ReadAction::class),
                    '{title}' => $catalog->getTitle(),
                ])
                ->setDomain('EkynaProduct');

            $event->addMessage($message);
        }
    }

    private function createMessage(ProductInterface $related, string $relation): ResourceMessage
    {
        return ResourceMessage::create('product.message.relation_prevents_deletion', ResourceMessage::TYPE_ERROR)
            ->setParameters([
                '{relation}'    => $this->translator->trans($relation, [], 'EkynaProduct'),
                '{url}'         => $this->resourceHelper->generateResourcePath($related, ReadAction::class),
                '{designation}' => $related->getFullDesignation(true),
            ])
            ->setDomain('EkynaProduct');
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $product = $this->getProductFromEvent($event);

        $message = new ProductDeletion($product->getId());

        $this->messageQueue->addMessage($message);
    }

    /**
     * Returns the product from the event.
     */
    private function getProductFromEvent(ResourceEventInterface $event): ProductInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof ProductInterface) {
            throw new UnexpectedTypeException($resource, ProductInterface::class);
        }

        return $resource;
    }
}

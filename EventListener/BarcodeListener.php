<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Event\BarcodeEvent;
use Ekyna\Bundle\AdminBundle\Model\BarcodeResult;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class BarcodeEventListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BarcodeListener implements EventSubscriberInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $repository;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $repository
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(ProductRepositoryInterface $repository, UrlGeneratorInterface $urlGenerator)
    {
        $this->repository = $repository;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Barcode event handler.
     *
     * @param BarcodeEvent $event
     */
    public function onBarcode(BarcodeEvent $event): void
    {
        $products = $this->repository->findBySkuOrReferences($event->getBarcode());

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
        foreach ($products as $product) {
            $event->addResult(new BarcodeResult(
                BarcodeResult::TYPE_REDIRECT,
                $product->getFullDesignation(true),
                $this->urlGenerator->generate('ekyna_product_product_admin_show', [
                    'productId' => $product->getId(),
                ])
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            BarcodeEvent::NAME => ['onBarcode', 0],
        ];
    }
}

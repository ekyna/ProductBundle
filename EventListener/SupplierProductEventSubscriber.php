<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Commerce\Supplier\Event\SupplierProductEvents;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SupplierProductEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;


    /**
     * Constructor.
     *
     * @param RequestStack               $requestStack
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(RequestStack $requestStack, ProductRepositoryInterface $productRepository)
    {
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
    }

    /**
     * Initialize event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInitialize(ResourceEventInterface $event)
    {
        /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface $supplierProduct */
        $supplierProduct = $event->getResource();

        $request = $this->requestStack->getMasterRequest();
        if (null === $request) {
            return;
        }

        if (0 == $productId = intval($request->query->get('productId'))) {
            return;
        }

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
        if (null === $product = $this->productRepository->find($productId)) {
            return;
        }

        if (!in_array($product->getType(), [ProductTypes::TYPE_SIMPLE, ProductTypes::TYPE_VARIANT])) {
            return;
        }

        $supplierProduct
            ->setDesignation($product->getFullDesignation(true))
            ->setWeight($product->getWeight())
            ->getSubjectIdentity()
            ->setIdentifier($product->getId())
            ->setProvider(ProductProvider::NAME)
            ->setSubject($product);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SupplierProductEvents::INITIALIZE => ['onInitialize', 0],
        ];
    }
}

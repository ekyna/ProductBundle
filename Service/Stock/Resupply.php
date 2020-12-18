<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stock;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Commerce\Supplier\Repository;
use Ekyna\Component\Commerce\Supplier\Util\SupplierUtil;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;

/**
 * Class Resupply
 * @package Ekyna\Bundle\ProductBundle\Service\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Move to commerce component
 */
class Resupply
{
    /**
     * @var Repository\SupplierProductRepositoryInterface
     */
    private $referenceRepository;

    /**
     * @var ResourceOperatorInterface
     */
    private $referenceOperator;

    /**
     * @var Repository\SupplierOrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Repository\SupplierOrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var ResourceOperatorInterface
     */
    private $orderOperator;

    /**
     * @var Repository\SupplierDeliveryRepositoryInterface
     */
    private $deliveryRepository;

    /**
     * @var Repository\SupplierDeliveryItemRepositoryInterface
     */
    private $deliveryItemRepository;

    /**
     * @var ResourceOperatorInterface
     */
    private $deliveryOperator;

    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;

    /**
     * @var ResourceEventInterface
     */
    private $event;


    /**
     * Constructor.
     *
     * @param Repository\SupplierProductRepositoryInterface   $referenceRepository
     * @param ResourceOperatorInterface                       $referenceOperator
     * @param Repository\SupplierOrderRepositoryInterface     $orderRepository
     * @param Repository\SupplierOrderItemRepositoryInterface $orderItemRepository
     * @param ResourceOperatorInterface                       $orderOperator
     * @param Repository\SupplierDeliveryRepositoryInterface  $deliveryRepository
     * @param ResourceRepositoryInterface                     $deliveryItemRepository
     * @param ResourceOperatorInterface                       $deliveryOperator
     * @param SubjectHelperInterface                          $subjectHelper
     */
    public function __construct(
        Repository\SupplierProductRepositoryInterface $referenceRepository,
        ResourceOperatorInterface $referenceOperator,
        Repository\SupplierOrderRepositoryInterface $orderRepository,
        Repository\SupplierOrderItemRepositoryInterface $orderItemRepository,
        ResourceOperatorInterface $orderOperator,
        Repository\SupplierDeliveryRepositoryInterface $deliveryRepository,
        ResourceRepositoryInterface $deliveryItemRepository,
        ResourceOperatorInterface $deliveryOperator,
        SubjectHelperInterface $subjectHelper
    ) {
        $this->referenceRepository = $referenceRepository;
        $this->referenceOperator = $referenceOperator;
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderOperator = $orderOperator;
        $this->deliveryRepository = $deliveryRepository;
        $this->deliveryItemRepository = $deliveryItemRepository;
        $this->deliveryOperator = $deliveryOperator;
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * Returns the event.
     *
     * @return ResourceEventInterface|null
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Resupplies the given supplier product by creating a new supplier order
     * or adding it to the given supplier order.
     *
     * @param Model\SupplierProductInterface $reference
     * @param float                          $quantity
     * @param float                          $netPrice
     * @param Model\SupplierOrderInterface   $order
     * @param \DateTime                      $eda
     *
     * @return Model\SupplierOrderInterface|null
     */
    public function resupply(
        Model\SupplierProductInterface $reference,
        $quantity,
        $netPrice = null,
        Model\SupplierOrderInterface $order = null,
        \DateTime $eda = null
    ) {
        /** @var Model\SupplierOrderItemInterface $item */
        $item = null;

        if (null !== $order) {
            $item = $this
                ->orderItemRepository
                ->findOneBy([
                    'order'   => $order,
                    'product' => $reference,
                ]);
        } else {
            $supplier = $reference->getSupplier();
            $order = $this->orderRepository->createNew();
            $order->setSupplier($supplier);

            $this->orderOperator->initialize($order);
        }

        if (null === $netPrice) {
            $netPrice = $reference->getNetPrice();
        }

        if (null === $item) {
            $item = $this->orderItemRepository->createNew();
            $item
                ->setProduct($reference)
                ->setNetPrice($netPrice)
                ->setQuantity($quantity);

            $order->addItem($item);
        } else {
            $item->setQuantity($item->getQuantity() + $quantity);

            if ($netPrice > $item->getNetPrice()) {
                $item->setNetPrice($netPrice);
            }
        }

        if (null !== $eda) {
            $orderEda = $order->getEstimatedDateOfArrival();
            if (null === $orderEda || $eda > $orderEda) {
                $order->setEstimatedDateOfArrival($eda);
            }
        }

        $this->event = $this->orderOperator->persist($order);
        if ($this->event->hasErrors()) {
            return null;
        }

        return $order;
    }

    /**
     * Finds or create the supplier reference for the given subject and supplier (helper for tests).
     *
     * @param SubjectInterface        $subject
     * @param Model\SupplierInterface $supplier
     *
     * @return Model\SupplierProductInterface|null
     */
    public function findOrCreateReference(SubjectInterface $subject, Model\SupplierInterface $supplier)
    {
        $reference = $this->referenceRepository->findOneBySubjectAndSupplier($subject, $supplier);

        if ($reference) {
            return $reference;
        }

        $reference = $this->referenceRepository->createNew();
        $reference
            ->setReference($subject->getReference())
            ->setDesignation($subject->getDesignation())
            ->setSupplier($supplier);

        $this->subjectHelper->assign($reference, $subject);

        $this->event = $this->referenceOperator->create($reference);
        if ($this->event->hasErrors()) {
            return null;
        }

        return $reference;
    }

    /**
     * Submits the given supplier order (helper for tests).
     *
     * @param Model\SupplierOrderInterface $order
     * @param \DateTime                    $eda
     *
     * @return Model\SupplierOrderInterface|null
     */
    public function submitOrder(Model\SupplierOrderInterface $order, \DateTime $eda = null)
    {
        $order->setOrderedAt(new \DateTime());

        if ($eda) {
            $order->setEstimatedDateOfArrival($eda);
        }

        $this->event = $this->orderOperator->persist($order);
        if ($this->event->hasErrors()) {
            return null;
        }

        return $order;
    }

    /**
     * Delivers the given supplier order (helper for tests).
     *
     * @param Model\SupplierOrderInterface $order
     *
     * @return Model\SupplierDeliveryInterface|null
     */
    public function deliverOrder(Model\SupplierOrderInterface $order)
    {
        $delivery = $this->deliveryRepository->createNew();

        foreach ($order->getItems() as $item) {
            $deliveryItem = $this->deliveryItemRepository->createNew();
            $deliveryItem
                ->setOrderItem($item)
                ->setQuantity(SupplierUtil::calculateDeliveryRemainingQuantity($item))
                ->setGeocode('TEST');

            $delivery->addItem($deliveryItem);
        }

        $order->addDelivery($delivery);

        $this->event = $this->deliveryOperator->persist($delivery);
        if ($this->event->hasErrors()) {
            return null;
        }

        return $delivery;
    }
}

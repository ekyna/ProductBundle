<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stock;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Commerce\Supplier\Util\SupplierUtil;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;

/**
 * Class Resupply
 * @package Ekyna\Bundle\ProductBundle\Service\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    Move to commerce component
 */
class Resupply
{
    private FactoryFactoryInterface    $factoryFactory;
    private RepositoryFactoryInterface $repositoryFactory;
    private ManagerFactoryInterface    $managerFactory;
    private SubjectHelperInterface     $subjectHelper;
    private ?ResourceEventInterface    $event = null;

    public function __construct(
        FactoryFactoryInterface    $factoryFactory,
        RepositoryFactoryInterface $repositoryFactory,
        ManagerFactoryInterface    $managerFactory,
        SubjectHelperInterface     $subjectHelper
    ) {
        $this->factoryFactory = $factoryFactory;
        $this->repositoryFactory = $repositoryFactory;
        $this->managerFactory = $managerFactory;
        $this->subjectHelper = $subjectHelper;
    }

    public function getEvent(): ?ResourceEventInterface
    {
        return $this->event;
    }

    /**
     * Resupplies the given supplier product by creating a new supplier order
     * or adding it to the given supplier order.
     */
    public function resupply(
        Model\SupplierProductInterface $reference,
        Decimal                        $quantity,
        Decimal                        $netPrice = null,
        Model\SupplierOrderInterface   $order = null,
        DateTimeInterface              $eda = null
    ): ?Model\SupplierOrderInterface {
        /** @var Model\SupplierOrderItemInterface $item */
        $item = null;

        if (null !== $order) {
            $item = $this
                ->repositoryFactory
                ->getRepository(Model\SupplierOrderItemInterface::class)
                ->findOneBy([
                    'order'   => $order,
                    'product' => $reference,
                ]);
        } else {
            $supplier = $reference->getSupplier();
            /** @var Model\SupplierOrderInterface $order */
            $order = $this
                ->factoryFactory
                ->getFactory(Model\SupplierOrderInterface::class)
                ->createWithSupplier($supplier);
        }

        if (null === $netPrice) {
            $netPrice = clone $reference->getNetPrice();
        }

        if (null === $item) {
            /** @var Model\SupplierOrderItemInterface $item */
            $item = $this
                ->factoryFactory
                ->getFactory(Model\SupplierOrderItemInterface::class)
                ->create();

            $item
                ->setQuantity($quantity)
                ->setProduct($reference)
                ->setNetPrice($netPrice);

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

        $this->event = $this
            ->managerFactory
            ->getManager(Model\SupplierOrderInterface::class)
            ->save($order);

        if ($this->event->hasErrors()) {
            return null;
        }

        return $order;
    }

    /**
     * Finds or create the supplier reference for the given subject and supplier (helper for tests).
     */
    public function findOrCreateReference(
        SubjectInterface        $subject,
        Model\SupplierInterface $supplier
    ): ?Model\SupplierProductInterface {
        /** @var Model\SupplierProductInterface $reference */
        $reference = $this
            ->repositoryFactory
            ->getRepository(Model\SupplierProductInterface::class)
            ->findOneBySubjectAndSupplier($subject, $supplier);

        if ($reference) {
            return $reference;
        }

        /** @var Model\SupplierProductInterface $reference */
        $reference = $this
            ->factoryFactory
            ->getFactory(Model\SupplierProductInterface::class)
            ->create();

        $reference
            ->setReference($subject->getReference())
            ->setDesignation($subject->getDesignation())
            ->setSupplier($supplier);

        $this->subjectHelper->assign($reference, $subject);

        $this->event = $this
            ->managerFactory
            ->getManager(Model\SupplierProductInterface::class)
            ->save($reference);

        if ($this->event->hasErrors()) {
            return null;
        }

        return $reference;
    }

    /**
     * Submits the given supplier order (helper for tests).
     */
    public function submitOrder(
        Model\SupplierOrderInterface $order,
        DateTimeInterface            $eda = null
    ): ?Model\SupplierOrderInterface {
        $order->setOrderedAt(new DateTime());

        if ($eda) {
            $order->setEstimatedDateOfArrival($eda);
        }

        $this->event = $this
            ->managerFactory
            ->getManager(Model\SupplierOrderInterface::class)
            ->save($order);

        if ($this->event->hasErrors()) {
            return null;
        }

        return $order;
    }

    /**
     * Delivers the given supplier order (helper for tests).
     */
    public function deliverOrder(Model\SupplierOrderInterface $order): ?Model\SupplierDeliveryInterface
    {
        $delivery = $this
            ->factoryFactory
            ->getFactory(Model\SupplierDeliveryInterface::class)
            ->create();

        foreach ($order->getItems() as $item) {
            $deliveryItem = $this
                ->factoryFactory
                ->getFactory(Model\SupplierDeliveryItemInterface::class)
                ->create();

            $deliveryItem
                ->setOrderItem($item)
                ->setQuantity(SupplierUtil::calculateDeliveryRemainingQuantity($item))
                ->setGeocode('TEST');

            $delivery->addItem($deliveryItem);
        }

        $order->addDelivery($delivery);

        $this->event = $this
            ->managerFactory
            ->getManager(Model\SupplierDeliveryInterface::class)
            ->save($delivery);

        if ($this->event->hasErrors()) {
            return null;
        }

        return $delivery;
    }
}

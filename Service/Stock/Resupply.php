<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stock;

use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
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
     * @var SupplierOrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ResourceRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var ResourceOperatorInterface
     */
    private $orderOperator;


    /**
     * Constructor.
     *
     * @param SupplierOrderRepositoryInterface $orderRepository
     * @param ResourceRepositoryInterface      $itemRepository
     * @param ResourceOperatorInterface        $orderOperator
     */
    public function __construct(
        SupplierOrderRepositoryInterface $orderRepository,
        ResourceRepositoryInterface $itemRepository,
        ResourceOperatorInterface $orderOperator
    ) {
        $this->orderRepository = $orderRepository;
        $this->itemRepository = $itemRepository;
        $this->orderOperator = $orderOperator;
    }

    /**
     * Resupplies the given supplier product by creating a new supplier order
     * or adding it to the given supplier order.
     *
     * @param Model\SupplierProductInterface $supplierProduct
     * @param float                          $quantity
     * @param float                          $netPrice
     * @param Model\SupplierOrderInterface   $supplierOrder
     * @param \DateTime                      $eda
     *
     * @return ResourceEventInterface
     */
    public function resupply(
        Model\SupplierProductInterface $supplierProduct,
        $quantity,
        $netPrice = null,
        Model\SupplierOrderInterface $supplierOrder = null,
        \DateTime $eda = null
    ) {
        /** @var Model\SupplierOrderItemInterface $supplierOrderItem */
        $supplierOrderItem = null;

        if (null !== $supplierOrder) {
            $supplierOrderItem = $this
                ->itemRepository
                ->findOneBy([
                    'order'   => $supplierOrder,
                    'product' => $supplierProduct,
                ]);
        } else {
            $supplier = $supplierProduct->getSupplier();
            $supplierOrder = $this->orderRepository->createNew();
            $supplierOrder->setSupplier($supplier);

            $this->orderOperator->initialize($supplierOrder);
        }

        if (null === $netPrice) {
            $netPrice = $supplierProduct->getNetPrice();
        }

        if (null === $supplierOrderItem) {
            $supplierOrderItem = $this->itemRepository->createNew();
            $supplierOrderItem
                ->setProduct($supplierProduct)
                ->setNetPrice($netPrice)
                ->setQuantity($quantity);

            $supplierOrder->addItem($supplierOrderItem);
        } else {
            $supplierOrderItem->setQuantity($supplierOrderItem->getQuantity() + $quantity);

            if ($netPrice > $supplierOrderItem->getNetPrice()) {
                $supplierOrderItem->setNetPrice($netPrice);
            }
        }

        if (null !== $eda) {
            $orderEda = $supplierOrder->getEstimatedDateOfArrival();
            if (null === $orderEda || $eda > $orderEda) {
                $supplierOrder->setEstimatedDateOfArrival($eda);
            }
        }

        return $this->orderOperator->persist($supplierOrder);
    }
}

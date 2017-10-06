<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stock;

use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Operator\ResourceOperator;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class Resupply
 * @package Ekyna\Bundle\ProductBundle\Service\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Resupply
{
    /**
     * @var SupplierOrderRepositoryInterface
     */
    private $supplierOrderRepository;

    /**
     * @var ResourceRepositoryInterface
     */
    private $supplierOrderItemRepository;

    /**
     * @var CurrencyRepositoryInterface
     */
    private $currencyRepository;

    /**
     * @var ResourceOperator
     */
    private $supplierOrderManager;


    /**
     * Constructor.
     *
     * @param SupplierOrderRepositoryInterface $supplierOrderRepository
     * @param ResourceRepositoryInterface      $supplierOrderItemRepository
     * @param CurrencyRepositoryInterface      $currencyRepository
     * @param ResourceOperator                 $supplierOrderManager
     */
    public function __construct(
        SupplierOrderRepositoryInterface $supplierOrderRepository,
        ResourceRepositoryInterface $supplierOrderItemRepository,
        CurrencyRepositoryInterface      $currencyRepository,
        ResourceOperator $supplierOrderManager
    ) {
        $this->supplierOrderRepository = $supplierOrderRepository;
        $this->supplierOrderItemRepository = $supplierOrderItemRepository;
        $this->currencyRepository = $currencyRepository;
        $this->supplierOrderManager = $supplierOrderManager;
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
        $netPrice,
        Model\SupplierOrderInterface $supplierOrder = null,
        \DateTime $eda = null
    ) {
        /** @var Model\SupplierOrderItemInterface $supplierOrderItem */
        $supplierOrderItem = null;

        if (null !== $supplierOrder) {
            $supplierOrderItem = $this
                ->supplierOrderItemRepository
                ->findOneBy([
                    'order'   => $supplierOrder,
                    'product' => $supplierProduct,
                ]);
        } else {
            $supplier = $supplierProduct->getSupplier();
            $supplierOrder = $this->supplierOrderRepository->createNew();
            $supplierOrder
                ->setCurrency($this->currencyRepository->findDefault())
                ->setSupplier($supplier)
                ->setCarrier($supplier->getCarrier());
        }

        if (null === $supplierOrderItem) {
            $supplierOrderItem = $this->supplierOrderItemRepository->createNew();
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

            if (null === $eda || $eda > $supplierOrder->getEstimatedDateOfArrival()) {
                $supplierOrder->setEstimatedDateOfArrival($eda);
            }
        }

        return $this->supplierOrderManager->persist($supplierOrder);
    }
}

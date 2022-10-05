<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\MessageHandler;

use Ekyna\Bundle\ProductBundle\Message\ProductDeletion;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Commerce\Cart\Manager\CartItemManagerInterface;
use Ekyna\Component\Commerce\Cart\Model\CartItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Manager\OrderItemManagerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Quote\Manager\QuoteItemManagerInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Supplier\Manager\SupplierOrderItemManagerInterface;
use Ekyna\Component\Commerce\Supplier\Manager\SupplierProductManagerInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;

/**
 * Class ProductDeletionHandler
 * @package Ekyna\Bundle\ProductBundle\MessageHandler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductDeletionHandler
{
    public function __construct(private readonly ManagerFactoryInterface $managerFactory)
    {
    }

    public function __invoke(ProductDeletion $message): void
    {
        /**
         * !!! Cascade removal made by database won't trigger delete events !!!
         */

        $identity = new SubjectIdentity(ProductProvider::getName(), $message->getProductId());

        $this->clearOrderItems($identity);
        $this->clearSupplierOrderItems($identity);
        $this->clearSupplierProducts($identity);

        $this->deleteCartItems($identity);
        $this->deleteQuoteItems($identity);
    }

    private function clearOrderItems(SubjectIdentity $identity): void
    {
        $manager = $this->managerFactory->getManager(OrderItemInterface::class);

        if (!$manager instanceof OrderItemManagerInterface) {
            throw new UnexpectedTypeException($manager, OrderItemManagerInterface::class);
        }

        $manager->clearSubjectIdentity($identity);
    }

    private function clearSupplierOrderItems(SubjectIdentity $identity): void
    {
        $manager = $this->managerFactory->getManager(SupplierOrderItemInterface::class);

        if (!$manager instanceof SupplierOrderItemManagerInterface) {
            throw new UnexpectedTypeException($manager, SupplierOrderItemManagerInterface::class);
        }

        $manager->clearSubjectIdentity($identity);
    }

    private function clearSupplierProducts(SubjectIdentity $identity): void
    {
        $manager = $this->managerFactory->getManager(SupplierProductInterface::class);

        if (!$manager instanceof SupplierProductManagerInterface) {
            throw new UnexpectedTypeException($manager, SupplierProductManagerInterface::class);
        }

        $manager->clearSubjectIdentity($identity);
    }

    private function deleteCartItems(SubjectIdentity $identity): void
    {
        $manager = $this->managerFactory->getManager(CartItemInterface::class);

        if (!$manager instanceof CartItemManagerInterface) {
            throw new UnexpectedTypeException($manager, CartItemManagerInterface::class);
        }

        $manager->removeBySubjectIdentity($identity);
    }

    private function deleteQuoteItems(SubjectIdentity $identity): void
    {
        $manager = $this->managerFactory->getManager(QuoteItemInterface::class);

        if (!$manager instanceof QuoteItemManagerInterface) {
            throw new UnexpectedTypeException($manager, QuoteItemManagerInterface::class);
        }

        $manager->removeBySubjectIdentity($identity);
    }
}

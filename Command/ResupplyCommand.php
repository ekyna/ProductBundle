<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Stock\Resupply;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierDeliveryItemRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierDeliveryRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class ResupplyCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResupplyCommand extends Command
{
    protected static $defaultName = 'ekyna:product:resupply';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SupplierProductRepositoryInterface
     */
    private $referenceRepository;

    /**
     * @var ResourceOperatorInterface
     */
    private $orderOperator;

    /**
     * @var SupplierDeliveryRepositoryInterface
     */
    private $deliveryRepository;

    /**
     * @var SupplierDeliveryItemRepositoryInterface
     */
    private $deliveryItemRepository;

    /**
     * @var ResourceOperatorInterface
     */
    private $deliveryOperator;

    /**
     * @var Resupply
     */
    private $resupply;

    /**
     * @var int[]
     */
    private $supplierOrderIds;

    /**
     * @var SupplierOrderInterface[]
     */
    private $supplierOrders;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface              $productRepository
     * @param SupplierProductRepositoryInterface      $referenceRepository
     * @param ResourceOperatorInterface               $orderOperator
     * @param SupplierDeliveryRepositoryInterface     $deliveryRepository
     * @param SupplierDeliveryItemRepositoryInterface $deliveryItemRepository
     * @param ResourceOperatorInterface               $deliveryOperator
     * @param Resupply                                $resupply
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SupplierProductRepositoryInterface $referenceRepository,
        ResourceOperatorInterface $orderOperator,
        SupplierDeliveryRepositoryInterface $deliveryRepository,
        SupplierDeliveryItemRepositoryInterface $deliveryItemRepository,
        ResourceOperatorInterface $deliveryOperator,
        Resupply $resupply
    ) {
        parent::__construct();

        $this->productRepository      = $productRepository;
        $this->referenceRepository    = $referenceRepository;
        $this->orderOperator          = $orderOperator;
        $this->deliveryRepository     = $deliveryRepository;
        $this->deliveryItemRepository = $deliveryItemRepository;
        $this->deliveryOperator       = $deliveryOperator;
        $this->resupply               = $resupply;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Resupply a product')
            ->addArgument('id', InputArgument::REQUIRED, 'The product id')
            ->addArgument('quantity', InputArgument::REQUIRED, 'The quantity');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id       = intval($input->getArgument('id'));
        $quantity = intval($input->getArgument('quantity'));

        if (0 >= $id || 1 > $quantity) {
            throw new InvalidArgumentException("Unexpected id or quantity.");
        }

        $product = $this->findProduct($id);

        $output->writeln('<comment>This command should not be used in a production environment.</comment>');
        $output->writeln('');

        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            "Resupply '{$product->getFullTitle()}' for '{$quantity}' quantity ?", false
        );
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $this->supplierOrderIds = [];
        $this->supplierOrders   = [];

        $output->writeln('');
        $output->writeln('### Creating supplier orders');
        $output->writeln('');

        $this->resupplyProduct($output, $product, $quantity);

        if (empty($this->supplierOrders)) {
            return;
        }

        $output->writeln('');
        $output->writeln('### Submitting supplier orders');
        $output->writeln('');

        $this->submitOrders($output);

        $output->writeln('');
        $output->writeln('### Creating deliveries');
        $output->writeln('');

        $this->createDeliveries($output);
    }

    /**
     * Submits the supplier orders.
     *
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function submitOrders(OutputInterface $output)
    {
        foreach ($this->supplierOrders as $order) {
            $name = $order->getNumber();

            $output->write(sprintf('<comment>%s</comment> %s ',
                $name,
                str_pad('.', 80 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            $order->setOrderedAt(new \DateTime());

            $event = $this->orderOperator->update($order);

            if ($event->hasErrors()) {
                $output->writeln('<error>failed</error>');

                return;
            }

            $output->writeln('<info>success</info>');
        }
    }

    /**
     * Creates the supplier orders deliveries.
     *
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function createDeliveries(OutputInterface $output)
    {
        foreach ($this->supplierOrders as $order) {
            $name = $order->getNumber();

            $output->write(sprintf('<comment>%s</comment> %s ',
                $name,
                str_pad('.', 80 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            /** @var SupplierDeliveryInterface $delivery */
            $delivery = $this->deliveryRepository->createNew();
            $delivery->setOrder($order);

            foreach ($order->getItems() as $orderItem) {
                /** @var SupplierDeliveryItemInterface $deliveryItem */
                $deliveryItem = $this->deliveryItemRepository->createNew();

                $deliveryItem
                    ->setOrderItem($orderItem)
                    ->setQuantity($orderItem->getQuantity())
                    ->setGeocode('TEST');

                $delivery->addItem($deliveryItem);
            }

            $event = $this->deliveryOperator->create($delivery);

            if ($event->hasErrors()) {
                $output->writeln('<error>failed</error>');

                return;
            }

            $output->writeln('<info>success</info>');
        }
    }

    /**
     * Finds the product.
     *
     * @param int $id
     *
     * @return ProductInterface
     */
    private function findProduct($id)
    {
        /** @var ProductInterface $product */
        $product = $this->productRepository->find($id);

        if (null === $product) {
            throw new InvalidArgumentException("Product not found for id " . $id);
        }

        return $product;
    }

    /**
     * Resupplies the given product regarding to his type.
     *
     * @param OutputInterface  $output
     * @param ProductInterface $product
     * @param float            $quantity
     */
    private function resupplyProduct(OutputInterface $output, ProductInterface $product, $quantity)
    {
        switch ($product->getType()) {
            case ProductTypes::TYPE_SIMPLE:
            case ProductTypes::TYPE_VARIANT:
                $this->resupplySimple($output, $product, $quantity);
                break;
            case ProductTypes::TYPE_VARIABLE:
                $this->resupplyVariable($output, $product, $quantity);
                break;
            case ProductTypes::TYPE_BUNDLE:
            case ProductTypes::TYPE_CONFIGURABLE:
                $this->resupplyBundle($output, $product, $quantity);
                break;
        }

        foreach ($product->getOptionGroups() as $optionGroup) {
            foreach ($optionGroup->getOptions() as $option) {
                if (null !== $op = $option->getProduct()) {
                    $this->resupplyProduct($output, $op, $quantity);
                }
            }
        }
    }

    /**
     * Resupplies the given simple/variant product.
     *
     * @param OutputInterface  $output
     * @param ProductInterface $product
     * @param float            $quantity
     */
    private function resupplySimple(OutputInterface $output, ProductInterface $product, $quantity)
    {
        ProductTypes::assertChildType($product);

        $name = sprintf('[%s] %s', $product->getId(), $product->getFullTitle());

        $output->write(sprintf('<comment>%s</comment> %s ',
            $name,
            str_pad('.', 80 - mb_strlen($name), '.', STR_PAD_LEFT)
        ));

        if (0 >= $diff = $quantity - $product->getAvailableStock()) {
            $output->writeln('<comment>skipped</comment>');

            return;
        }

        $references = $this->referenceRepository->findBySubject($product);

        if (empty($references)) {
            $output->writeln('<error>no ref</error>');

            return;
        }

        $reference = reset($references);

        $supplierOrder = null;
        foreach ($this->supplierOrders as $order) {
            if ($order->getSupplier() === $reference->getSupplier()) {
                $supplierOrder = $order;
            }
        }

        $supplierOrder = $this->resupply->resupply($reference, $diff, null, $supplierOrder);

        if (null === $supplierOrder) {
            $output->writeln('<error>failed</error>');

            return;
        }

        if (!in_array($supplierOrder->getId(), $this->supplierOrderIds, true)) {
            $this->supplierOrderIds[] = $supplierOrder->getId();
            $this->supplierOrders[]   = $supplierOrder;
        }

        $output->writeln('<info>success</info>');
    }

    /**
     * Resupplies all the given product's variants.
     *
     * @param OutputInterface  $output
     * @param ProductInterface $product
     * @param float            $quantity
     */
    private function resupplyVariable(OutputInterface $output, ProductInterface $product, $quantity)
    {
        ProductTypes::assertVariable($product);

        foreach ($product->getVariants() as $variant) {
            $this->resupplyProduct($output, $variant, $quantity);
        }
    }

    /**
     * Resupplies all the given product's bundle slots choices.
     *
     * @param OutputInterface  $output
     * @param ProductInterface $product
     * @param float            $quantity
     */
    private function resupplyBundle(OutputInterface $output, ProductInterface $product, $quantity)
    {
        ProductTypes::assertBundled($product);

        foreach ($product->getBundleSlots() as $slot) {
            foreach ($slot->getChoices() as $choice) {
                $this->resupplyProduct($output, $choice->getProduct(), $choice->getMinQuantity() * $quantity);
            }
        }
    }
}

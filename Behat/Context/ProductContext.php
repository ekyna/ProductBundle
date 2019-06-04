<?php

namespace Ekyna\Bundle\ProductBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class ProductContext
 * @package Ekyna\Bundle\ProductBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following products:
     *
     * @param TableNode $table
     */
    public function createProducts(TableNode $table)
    {
        $products = $this->castProductsTable($table);

        $manager = $this->getContainer()->get('ekyna_product.product.manager');

        foreach ($products as $product) {
            $manager->persist($product);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castProductsTable(TableNode $table)
    {
        $repository = $this->getContainer()->get('ekyna_product.product.repository');
        $taxGroup = $this->getContainer()->get('ekyna_commerce.tax_group.repository')->findDefault();

        /** @var \Ekyna\Bundle\ProductBundle\Model\BrandInterface $brand */
        $brand = $this->getContainer()->get('ekyna_product.brand.repository')->findOneBy(['name' => 'Acme']);
        /** @var \Ekyna\Bundle\ProductBundle\Model\CategoryInterface $category */
        $category = $this->getContainer()->get('ekyna_product.category.repository')->findOneBy(['name' => 'Dummies']);


        $products = [];
        foreach ($table->getHash() as $hash) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
            $product = $repository->createNew();
            $product
                ->setType($hash['type'])
                ->setDesignation($hash['designation'])
                ->setReference($hash['reference'])
                ->setNetPrice($hash['netPrice'])
                ->setWeight($hash['weight'])
                ->setBrand($brand)
                ->addCategory($category)
                ->setTaxGroup($taxGroup)
                ->setVisible(true)
                ->translate()
                    ->setTitle($hash['designation']);

            $products[] = $product;
        }

        return $products;
    }

    /**
     * @Given The following products are resupplied:
     *
     * @param TableNode $table
     */
    public function supplierResupplyProducts(TableNode $table)
    {
        $supplierRepository = $this->getContainer()->get('ekyna_commerce.supplier.repository');
        $productRepository = $this->getContainer()->get('ekyna_product.product.repository');
        $resupply = $this->getContainer()->get('ekyna_product.resupply');

        $orders = [];
        foreach ($table->getHash() as $hash) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierInterface $supplier */
            if (null === $supplier = $supplierRepository->findOneBy(['name' => $hash['supplier']])) {
                throw new \Exception("Supplier with name '{$hash['supplier']}' not found.");
            }

            /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
            if (null === $product = $productRepository->findOneBy(['reference' => $hash['reference']])) {
                throw new \Exception("Product with reference '{$hash['reference']}' not found.");
            }

            if (null === $reference = $resupply->findOrCreateReference($product, $supplier)) {
                throw new \Exception("Failed to find or create supplier reference");
            }

            if (null === $order = $resupply->resupply($reference, $hash['quantity'])) {
                throw new \Exception("Failed to resupply supplier reference");
            }

            if (!in_array($order, $orders)) {
                $orders[] = $order;
            }
        }

        foreach ($orders as $order) {
            if (null === $resupply->submitOrder($order, new \DateTime('+2 days'))) {
                throw new \Exception("Failed to submit order");
            }
        }

        foreach ($orders as $order) {
            if (null === $resupply->deliverOrder($order)) {
                throw new \Exception("Failed to deliver order");
            }
        }
    }
}

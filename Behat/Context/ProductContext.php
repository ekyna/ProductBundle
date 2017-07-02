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
    public function createBrands(TableNode $table)
    {
        $products = $this->castBrandsTable($table);

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
    private function castBrandsTable(TableNode $table)
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
                ->translate()
                    ->setTitle($hash['designation']);

            $products[] = $product;
        }

        return $products;
    }
}

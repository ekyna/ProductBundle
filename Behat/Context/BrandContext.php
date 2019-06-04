<?php

namespace Ekyna\Bundle\ProductBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class BrandContext
 * @package Ekyna\Bundle\ProductBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BrandContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following brands:
     *
     * @param TableNode $table
     */
    public function createBrands(TableNode $table)
    {
        $brands = $this->castBrandsTable($table);

        $manager = $this->getContainer()->get('ekyna_product.brand.manager');

        foreach ($brands as $brand) {
            $manager->persist($brand);
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
        $repository = $this->getContainer()->get('ekyna_product.brand.repository');

        $brands = [];
        foreach ($table->getHash() as $hash) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\BrandInterface $brand */
            $brand = $repository->createNew();
            $brand
                ->setName($hash['name'])
                ->setVisible(true)
                ->translate()
                    ->setTitle($hash['name']);

            $brands[] = $brand;
        }

        return $brands;
    }
}

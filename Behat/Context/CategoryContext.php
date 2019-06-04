<?php

namespace Ekyna\Bundle\ProductBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class CategoryContext
 * @package Ekyna\Bundle\ProductBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CategoryContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following categories:
     *
     * @param TableNode $table
     */
    public function createCategories(TableNode $table)
    {
        $categories = $this->castCategoriesTable($table);

        $manager = $this->getContainer()->get('ekyna_product.category.manager');

        foreach ($categories as $category) {
            $manager->persist($category);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castCategoriesTable(TableNode $table)
    {
        $repository = $this->getContainer()->get('ekyna_product.category.repository');

        $categories = [];
        foreach ($table->getHash() as $hash) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\CategoryInterface $category */
            $category = $repository->createNew();
            $category
                ->setName($hash['name'])
                ->setVisible(true)
                ->translate()
                    ->setTitle($hash['name']);

            $categories[] = $category;
        }

        return $categories;
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class AttributeGroupContext
 * @package Ekyna\Bundle\ProductBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeGroupContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following attribute groups:
     *
     * @param TableNode $table
     */
    public function createAttributeGroups(TableNode $table)
    {
        $attributeGroups = $this->castAttributeGroupsTable($table);

        $manager = $this->getContainer()->get('ekyna_product.attribute_group.manager');

        foreach ($attributeGroups as $group) {
            $manager->persist($group);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castAttributeGroupsTable(TableNode $table)
    {
        $repository = $this->getContainer()->get('ekyna_product.attribute_group.repository');

        $groups = [];
        foreach ($table->getHash() as $hash) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\AttributeGroupInterface $group */
            $group = $repository->createNew();
            $group
                ->setName($hash['name'])
                ->translate()
                    ->setTitle($hash['name']);

            $groups[] = $group;
        }

        return $groups;
    }
}

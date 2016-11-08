<?php

namespace Ekyna\Bundle\ProductBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class AttributeContext
 * @package Ekyna\Bundle\ProductBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following attributes:
     *
     * @param TableNode $table
     */
    public function createAttributes(TableNode $table)
    {
        $attributes = $this->castAttributesTable($table);

        $manager = $this->getContainer()->get('ekyna_product.attribute.manager');

        foreach ($attributes as $attribute) {
            $manager->persist($attribute);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castAttributesTable(TableNode $table)
    {
        $groupRepository = $this->getContainer()->get('ekyna_product.attribute_group.repository');
        $repository = $this->getContainer()->get('ekyna_product.attribute.repository');

        $attributes = [];
        foreach ($table->getHash() as $hash) {
            if (null === $group = $groupRepository->findOneBy(['name' => $hash['group']])) {
                throw new \InvalidArgumentException("Failed to find an attribute group named '{$hash['group']}'.");
            }

            /** @var \Ekyna\Bundle\ProductBundle\Model\AttributeInterface $attribute */
            $attribute = $repository->createNew();
            $attribute
                ->setName($hash['name'])
                ->setGroup($group);

            $attributes[] = $attribute;
        }

        return $attributes;
    }
}

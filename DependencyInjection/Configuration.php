<?php

namespace Ekyna\Bundle\ProductBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Configuration
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ekyna_product');

        $this->addPoolsSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Adds `pools` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addPoolsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('options')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('label')->end()
                            ->scalarNode('class')->end()
                            ->scalarNode('form_type')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('products')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('label')->end()
                            ->scalarNode('class')->end()
                            ->arrayNode('options')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('pools')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('category')
                            ->isRequired()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('templates')->defaultValue('EkynaProductBundle:Category/Admin')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Category')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\CategoryController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\CategoryRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\CategoryType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\CategoryType')->end()
                            ->end()
                        ->end()
                        ->arrayNode('tax')
                            ->isRequired()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('templates')->defaultValue('EkynaProductBundle:Tax/Admin')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Tax')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\TaxType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\TaxType')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}

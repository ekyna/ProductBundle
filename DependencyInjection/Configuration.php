<?php

namespace Ekyna\Bundle\ProductBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Class Configuration
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection
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

        $rootNode
            ->children()
                ->scalarNode('option_class')->defaultValue('Ekyna\Bundle\CmsBundle\Entity\AbstractOption')->end()
                ->scalarNode('product_class')->defaultValue('Ekyna\Bundle\CmsBundle\Entity\AbstractProduct')->end()
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
            ->end()
        ;

        return $treeBuilder;
    }
}

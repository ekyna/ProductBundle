<?php

namespace Ekyna\Bundle\ProductBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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

        $this->addDefaultSection($rootNode);
        $this->addPoolsSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Adds `default` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addDefaultSection(ArrayNodeDefinition $node)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $node
            ->children()
                ->arrayNode('default')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('no_image')->defaultValue('/bundles/ekynaproduct/img/no-image.jpg')->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Adds `pools` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addPoolsSection(ArrayNodeDefinition $node)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $node
            ->children()
                ->arrayNode('pools')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('attribute')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue('EkynaProductBundle:Admin/Attribute')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Attribute')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Product\AttributeType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\AttributeType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.attribute_group')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('attribute_group')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    'show.html'  => 'EkynaProductBundle:Admin/AttributeGroup:show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\AttributeGroup')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Product\AttributeGroupType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\AttributeGroupType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('attribute_set')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    'show.html' => 'EkynaProductBundle:Admin/AttributeSet:show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\AttributeSet')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Product\AttributeSetType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\AttributeSetType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('brand')
                            ->isRequired()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => 'EkynaProductBundle:Admin/Brand:_form.html',
                                    'show.html'  => 'EkynaProductBundle:Admin/Brand:show.html',
                                ])->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Brand')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\BrandType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\BrandType')->end()
                            ->end()
                        ->end()
                        ->arrayNode('bundle_choice')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\BundleChoice')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.bundle_slot')->end()
                            ->end()
                        ->end()
                        ->arrayNode('bundle_choice_rule')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\BundleChoiceRule')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.bundle_choice')->end()
                            ->end()
                        ->end()
                        ->arrayNode('bundle_slot')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\BundleSlot')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\BundleSlotTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title', 'description'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('category')
                            ->isRequired()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'     => 'EkynaProductBundle:Admin/Category:_form.html',
                                    'show.html'      => 'EkynaProductBundle:Admin/Category:show.html',
                                    'new_child.html' => 'EkynaProductBundle:Admin/Category:new_child.html',
                                ])->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Category')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\CategoryController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\CategoryRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\CategoryType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\CategoryType')->end()
                            ->end()
                        ->end()
                        ->arrayNode('product')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue('EkynaProductBundle:Admin/Product')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Product')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\ProductController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\ProductRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Product\ProductType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\ProductType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\ProductTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title', 'description'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('product_image')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\ProductImage')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                            ->end()
                        ->end()
                        ->arrayNode('product_stock_unit')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\ProductStockUnit')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\ProductStockUnitRepository')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}

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
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\AttributeController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\AttributeRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\AttributeType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\AttributeType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.attribute_group')->end()
                                ->scalarNode('event')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\AttributeTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('attribute_group')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => 'EkynaProductBundle:Admin/AttributeGroup:_form.html',
                                    'show.html'  => 'EkynaProductBundle:Admin/AttributeGroup:show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\AttributeGroup')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\AttributeGroupController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\AttributeGroupRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\AttributeGroupType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\AttributeGroupType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\AttributeGroupTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title'])
                                        ->end()
                                    ->end()
                                ->end()
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
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\AttributeSetType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\AttributeSetType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('brand')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => 'EkynaProductBundle:Admin/Brand:_form.html',
                                    'show.html'  => 'EkynaProductBundle:Admin/Brand:show.html',
                                ])->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Brand')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\BrandController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\BrandRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\BrandType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\BrandType')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\BrandTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title', 'description'])
                                        ->end()
                                    ->end()
                                ->end()
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
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'     => 'EkynaProductBundle:Admin/Category:_form.html',
                                    'list.html'      => 'EkynaProductBundle:Admin/Category:list.html',
                                    'new.html'       => 'EkynaProductBundle:Admin/Category:new.html',
                                    'new_child.html' => 'EkynaProductBundle:Admin/Category:new_child.html',
                                    'show.html'      => 'EkynaProductBundle:Admin/Category:show.html',
                                    'edit.html'      => 'EkynaProductBundle:Admin/Category:edit.html',
                                    'remove.html'    => 'EkynaProductBundle:Admin/Category:remove.html',
                                ])->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Category')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\CategoryController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\CategoryRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\CategoryType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\CategoryType')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\CategoryTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title', 'description'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('option')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Option')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\OptionRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\OptionType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.option_group')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\OptionTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title', 'description'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('option_group')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\OptionGroup')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\OptionGroupRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\OptionGroupType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\OptionGroupTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title', 'description'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('pricing')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => 'EkynaProductBundle:Admin/Pricing:_form.html',
                                    'show.html'  => 'EkynaProductBundle:Admin/Pricing:show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Pricing')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\PricingRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\PricingType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\PricingType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('pricing_rule')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\PricingRule')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\PricingRuleType')->end()
                            ->end()
                        ->end()
                        ->arrayNode('product')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue('EkynaProductBundle:Admin/Product')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Product')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\ProductController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\ProductRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\ProductType')->end()
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
                                            ->defaultValue(['title', 'description', 'attributesTitle'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('product_adjustment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\ProductAdjustment')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\ProductAdjustmentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                            ->end()
                        ->end()
                        ->arrayNode('product_media')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\ProductMedia')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                            ->end()
                        ->end()
                        ->arrayNode('product_reference')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\ProductReference')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\ProductReferenceType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                            ->end()
                        ->end()
                        ->arrayNode('product_stock_unit')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue('EkynaProductBundle:Admin/ProductStockUnit')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\ProductStockUnit')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\ProductStockUnitRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\ProductStockUnitType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\ProductStockUnitType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}

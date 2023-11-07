<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\DependencyInjection;

use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRegistry;
use Ekyna\Bundle\ProductBundle\Service\Features;
use Ekyna\Bundle\ProductBundle\Service\Generator\Gtin13Generator;
use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('ekyna_product');

        $root = $builder->getRootNode();

        $this->addDefaultSection($root);
        $this->addFeatureSection($root);
        $this->addCatalogSection($root);
        $this->addEditorSection($root);
        $this->addPricingSection($root);
        $this->addHighlightSection($root);
        $this->addAdjustmentSection($root);

        return $builder;
    }

    private function addDefaultSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('default')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('no_image')
                            ->cannotBeEmpty()
                            ->defaultValue('/bundles/ekynaproduct/img/no-image.gif')
                        ->end()
                        ->scalarNode('sale_item_form_theme')
                            ->cannotBeEmpty()
                            ->defaultValue('@EkynaProduct/Form/sale_item_configure.html.twig')
                        ->end()
                        ->scalarNode('cart_success_template')
                            ->cannotBeEmpty()
                            ->defaultValue('@EkynaProduct/Cart/success.html.twig')
                        ->end()
                        ->integerNode('cache_ttl')
                            ->defaultValue(3600)
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addFeatureSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('feature')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode(Features::COMPONENT)
                            ->canBeEnabled()
                        ->end()
                        /* TODO ->arrayNode(Features::COMPATIBILITY)
                            ->canBeEnabled()
                        ->end()*/
                        ->arrayNode(Features::GTIN13_GENERATOR)
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('manufacturer')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('class')
                                    ->cannotBeEmpty()
                                    ->defaultValue(Gtin13Generator::class)
                                    ->validate()
                                        ->ifTrue(function($value) {
                                            return !is_subclass_of($value, GeneratorInterface::class);
                                        })
                                        ->thenInvalid('Class %s must implements ' . GeneratorInterface::class)
                                    ->end()
                                ->end()
                                ->scalarNode('path')
                                    ->cannotBeEmpty()
                                    ->defaultValue('%kernel.project_dir%/var/data/gtin13_number')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addCatalogSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('catalog')
                    ->addDefaultsIfNotSet()
                    ->treatFalseLike([
                        'enabled'   => false,
                        'account'   => false,
                        'themes'    => [],
                        'templates' => [],
                    ])
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('account')
                            ->defaultFalse()
                        ->end()
                        ->arrayNode('themes')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('label')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('css')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                            ->defaultValue(CatalogRegistry::getDefaultThemes())
                        ->end()
                        ->arrayNode('templates')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('label')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('directory')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('mockup')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('form_type')->defaultNull()->end()
                                    ->integerNode('slots')->defaultValue(0)->end()
                                ->end()
                            ->end()
                            ->defaultValue(CatalogRegistry::getDefaultTemplates())
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addEditorSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('editor')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('slide')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('template')
                                    ->cannotBeEmpty()
                                    ->defaultValue('@EkynaProduct/Editor/Block/product_slide.html.twig')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addHighlightSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('highlight')
                    ->canBeDisabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('thumb_template')
                            ->cannotBeEmpty()
                            ->defaultValue('@EkynaProduct/Highlight/thumb.html.twig')
                        ->end()
                        ->arrayNode('best_seller')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('template')
                                    ->cannotBeEmpty()
                                    ->defaultValue('@EkynaProduct/Highlight/best.html.twig')
                                ->end()
                                ->scalarNode('from')
                                    ->cannotBeEmpty()
                                    ->defaultValue('-6 months')
                                ->end()
                                ->integerNode('limit')
                                    ->defaultValue(8)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('cross_selling')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('template')
                                    ->cannotBeEmpty()
                                    ->defaultValue('@EkynaProduct/Highlight/cross.html.twig')
                                ->end()
                                ->scalarNode('from')
                                    ->cannotBeEmpty()
                                    ->defaultValue('-6 months')
                                ->end()
                                ->integerNode('limit')
                                    ->defaultValue(4)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addPricingSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('pricing')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('final_price_format')
                            ->cannotBeEmpty()
                            ->defaultValue('<strong>{amount}</strong>&nbsp;<sup>{mode}</sup>')
                        ->end()
                        ->scalarNode('original_price_format')
                            ->cannotBeEmpty()
                            ->defaultValue('<del>{amount}</del>&nbsp;')
                        ->end()
                        ->booleanNode('price_with_from')
                            ->defaultTrue()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addAdjustmentSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('adjustment')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                    ->children()
                        ->scalarNode('label')->end()
                        ->scalarNode('domain')->end()
                    ->end()
                    ->end()
                ->end()
            ->end();
    }
}

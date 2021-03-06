<?php

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
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ekyna_product');

        $this->addDefaultSection($rootNode);
        $this->addFeatureSection($rootNode);
        $this->addCatalogSection($rootNode);
        $this->addEditorSection($rootNode);
        $this->addPricingSection($rootNode);
        $this->addHighlightSection($rootNode);
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

    /**
     * Adds `feature` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addFeatureSection(ArrayNodeDefinition $node)
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
                                        ->thenInvalid("Class %s must implements " . GeneratorInterface::class)
                                    ->end()
                                ->end()
                                ->scalarNode('path')
                                    ->cannotBeEmpty()
                                    ->defaultValue('%kernel.data_dir%/gtin13_number')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Adds `catalog` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addCatalogSection(ArrayNodeDefinition $node)
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

    /**
     * Adds `editor` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addEditorSection(ArrayNodeDefinition $node)
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

    /**
     * Adds `highlight` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addHighlightSection(ArrayNodeDefinition $node)
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

    /**
     * Adds `pricing` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addPricingSection(ArrayNodeDefinition $node)
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
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Attribute\AttributeType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\AttributeType')->end()
                                ->scalarNode('parent')->end()
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
                        ->arrayNode('attribute_choice')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue('EkynaProductBundle:Admin/AttributeChoice')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\AttributeChoice')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\AttributeChoiceController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\AttributeChoiceRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Attribute\AttributeChoiceType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\AttributeChoiceType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.attribute')->end()
                                ->scalarNode('event')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\AttributeChoiceTranslation')->end()
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
                                    'show.html' => '@EkynaProduct/Admin/AttributeSet/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\AttributeSet')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Attribute\AttributeSetType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\AttributeSetType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('brand')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaProduct/Admin/Brand/_form.html',
                                    'show.html'  => '@EkynaProduct/Admin/Brand/show.html',
                                ])->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Brand')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\BrandController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\BrandRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Brand\BrandType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\BrandType')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\BrandTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title', 'description', 'slug'])
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
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_product.bundle_rule')->end()
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
                        ->arrayNode('bundle_slot_rule')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\BundleSlotRule')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.bundle_slot')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_product.bundle_rule')->end()
                            ->end()
                        ->end()
                        ->arrayNode('catalog')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaProduct/Admin/Catalog/_form.html',
                                    'show.html'   => '@EkynaProduct/Admin/Catalog/show.html',
                                    'render.html' => '@EkynaProduct/Admin/Catalog/render.html',
                                ])->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Catalog')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\CatalogController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\CatalogRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Catalog\CatalogType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\CatalogType')->end()
                                /*->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\CategoryTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title', 'description', 'slug'])
                                        ->end()
                                    ->end()
                                ->end()*/
                            ->end()
                        ->end()
                        ->arrayNode('category')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'     => '@EkynaProduct/Admin/Category/_form.html',
                                    'list.html'      => '@EkynaProduct/Admin/Category/list.html',
                                    'new_child.html' => '@EkynaProduct/Admin/Category/new_child.html',
                                    'show.html'      => '@EkynaProduct/Admin/Category/show.html',
                                ])->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Category')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\CategoryController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\CategoryRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Category\CategoryType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\CategoryType')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\CategoryTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title', 'description', 'slug'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('component')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Component')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Component\ComponentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                            ->end()
                        ->end()
                        ->arrayNode('cross_selling')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\CrossSelling')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\CrossSelling\CrossSellingType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                            ->end()
                        ->end()
                        ->arrayNode('option')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Option')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\OptionRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Option\OptionType')->end()
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
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Option\OptionGroupType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\OptionGroupTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('offer')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Offer')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\OfferRepository')->end()
                            ->end()
                        ->end()
                        ->arrayNode('price')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Price')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\PriceRepository')->end()
                            ->end()
                        ->end()
                        ->arrayNode('pricing')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaProduct/Admin/Pricing/_form.html',
                                    'show.html'  => '@EkynaProduct/Admin/Pricing/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Pricing')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\PricingRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Pricing\PricingType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\PricingType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('pricing_rule')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\PricingRule')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\Pricing\PricingRuleType')->end()
                            ->end()
                        ->end()
                        ->arrayNode('product')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'     => '@EkynaProduct/Admin/Product/_form.html',
                                    'list.html'      => '@EkynaProduct/Admin/Product/list.html',
                                    'export.html'    => '@EkynaProduct/Admin/Product/export.html',
                                    'new.html'       => '@EkynaProduct/Admin/Product/new.html',
                                    'show.html'      => '@EkynaProduct/Admin/Product/show.html',
                                    'edit.html'      => '@EkynaProduct/Admin/Product/edit.html',
                                    'duplicate.html' => '@EkynaProduct/Admin/Product/duplicate.html',
                                    'convert.html'   => '@EkynaProduct/Admin/Product/convert.html',
                                    'remove.html'    => '@EkynaProduct/Admin/Product/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\Product')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\ProductController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\ProductRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\ProductType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\ProductType')->end()
                                ->scalarNode('parent')->end()
                                ->arrayNode('event')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->integerNode('priority')->defaultValue(-1024)->end()
                                    ->end()
                                ->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\ProductTranslation')->end()
                                        ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\ProductTranslationRepository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title', 'subTitle', 'attributesTitle', 'description', 'slug'])
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
                        ->arrayNode('product_attribute')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\ProductAttribute')->end()
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
                        ->arrayNode('product_mention')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\ProductMention')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\ProductMentionType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\ProductMentionTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['content'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('product_reference')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\ProductReference')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\ProductReferenceRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\ProductReferenceType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                            ->end()
                        ->end()
                        ->arrayNode('product_stock_unit')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue('EkynaProductBundle:Admin/ProductStockUnit')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\ProductStockUnit')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\StockUnitController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\ProductStockUnitRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\ProductStockUnitType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_product.product')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.stock_unit')->end()
                                ->arrayNode('event')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->integerNode('priority')->defaultValue(-1280)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('special_offer')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaProduct/Admin/SpecialOffer/_form.html',
                                    'show.html'  => '@EkynaProduct/Admin/SpecialOffer/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\ProductBundle\Entity\SpecialOffer')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\ProductBundle\Controller\Admin\SpecialOfferController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\ProductBundle\Repository\SpecialOfferRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\ProductBundle\Form\Type\SpecialOffer\SpecialOfferType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\ProductBundle\Table\Type\SpecialOfferType')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}

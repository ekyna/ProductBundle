<?php

namespace Ekyna\Bundle\ProductBundle;

use Ekyna\Bundle\ResourceBundle\AbstractBundle;
use Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EkynaProductBundle
 * @package Ekyna\Bundle\ProductBundle
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaProductBundle extends AbstractBundle
{
    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new Compiler\RegisterProductEventHandlerPass());
        $container->addCompilerPass(new Compiler\AttributeTypeRegistryPass());
        $container->addCompilerPass(new Compiler\AdminMenuPass());
    }

    /**
     * @inheritdoc
     */
    protected function getModelInterfaces()
    {
        return [
            Model\AttributeInterface::class         => 'ekyna_product.attribute.class',
            Model\AttributeChoiceInterface::class   => 'ekyna_product.attribute_choice.class',
            Model\AttributeSetInterface::class      => 'ekyna_product.attribute_set.class',
            Model\AttributeSlotInterface::class     => 'ekyna_product.attribute_slot.class',
            Model\BrandInterface::class             => 'ekyna_product.brand.class',
            Model\BundleChoiceInterface::class      => 'ekyna_product.bundle_choice.class',
            Model\BundleChoiceRuleInterface::class  => 'ekyna_product.bundle_choice_rule.class',
            Model\BundleSlotInterface::class        => 'ekyna_product.bundle_slot.class',
            Model\CategoryInterface::class          => 'ekyna_product.category.class',
            Model\OptionInterface::class            => 'ekyna_product.option.class',
            Model\OptionGroupInterface::class       => 'ekyna_product.option_group.class',
            Model\PricingInterface::class           => 'ekyna_product.pricing.class',
            Model\PricingRuleInterface::class       => 'ekyna_product.pricing_rule.class',
            Model\ProductInterface::class           => 'ekyna_product.product.class',
            Model\ProductAdjustmentInterface::class => 'ekyna_product.product_adjustment.class',
            Model\ProductAttributeInterface::class  => 'ekyna_product.product_attribute.class',
            Model\ProductMediaInterface::class      => 'ekyna_product.product_media.class',
            Model\ProductReferenceInterface::class  => 'ekyna_product.product_reference.class',
            Model\ProductStockUnitInterface::class  => 'ekyna_product.product_stock_unit.class',
        ];
    }
}

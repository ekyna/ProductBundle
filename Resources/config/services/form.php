<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Form\EventListener\ProductTypeSubscriber;
use Ekyna\Bundle\ProductBundle\Form\Extension\SaleItemConfigureTypeExtension;
use Ekyna\Bundle\ProductBundle\Form\ProductFormBuilder;
use Ekyna\Bundle\ProductBundle\Form\Type\Attribute;
use Ekyna\Bundle\ProductBundle\Form\Type\Bundle;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog;
use Ekyna\Bundle\ProductBundle\Form\Type\Convert\VariableType;
use Ekyna\Bundle\ProductBundle\Form\Type\Editor;
use Ekyna\Bundle\ProductBundle\Form\Type\ExportConfigType;
use Ekyna\Bundle\ProductBundle\Form\Type\Option;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductAdjustmentType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductAttributesType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductType;
use Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;
use Ekyna\Bundle\ProductBundle\Form\Type\StockView;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services
        ->set('ekyna_product.builder.product_form', ProductFormBuilder::class)
        ->args([
            service('ekyna_product.features'),
            param('ekyna_product.class.product_media'),
        ]);

    $services
        ->set('ekyna_product.listener.product_form', ProductTypeSubscriber::class)
        ->args([
            service('ekyna_product.builder.product_form'),
            service('ekyna_commerce.builder.subject_form'),
            service('ekyna_commerce.builder.stock_subject_form'),
            service('ekyna_product.repository.attribute_set'),
        ]);

    $services
        ->set('ekyna_product.form_type.stock_view', StockView\InventoryType::class)
        ->args([
            service('ekyna_product.repository.brand'),
            service('ekyna_commerce.repository.supplier'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.export.config', ExportConfigType::class)
        ->args([
            service('translator'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.stock_view.quick_edit', StockView\QuickEditType::class)
        ->args([
            service('ekyna_commerce.builder.subject_form'),
            service('ekyna_commerce.builder.stock_subject_form'),
            service('ekyna_commerce.resolver.tax'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.stock_view.batch_edit', StockView\BatchEditType::class)
        ->args([
            service('ekyna_commerce.builder.stock_subject_form'),
        ])
        ->tag('form.type')
        ->tag('form.js', [
            'selector' => 'form[name=batch_edit]',
            'path'     => 'ekyna-product/form/batch-edit',
        ]);

    $services
        ->set('ekyna_product.form_type.stock_view.resupply', StockView\ResupplyType::class)
        ->args([
            service('ekyna_commerce.repository.supplier_product'),
        ])
        ->tag('form.type')
        ->tag('form.js', [
            'selector' => 'form[name=ekyna_product_inventory_resupply]',
            'path'     => 'ekyna-product/form/resupply',
        ]);

    $services
        ->set('ekyna_product.form_type.stock_view.resupply_product', StockView\ResupplyProductType::class)
        ->args([
            service('ekyna_commerce.repository.supplier_order'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.attribute', Attribute\AttributeType::class)
        ->args([
            service('ekyna_product.registry.attribute_type'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.attribute.unit', Attribute\Type\UnitAttributeType::class)
        ->args([
            service('translator'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.bundle_slots', Bundle\BundleSlotsType::class)
        ->tag('form.type')
        ->tag('form.js', [
            'selector' => '.product-bundle-slots',
            'path'     => 'ekyna-product/form/product-bundle-slots',
        ]);

    $services
        ->set('ekyna_product.form_type.bundle_slot', Bundle\BundleSlotType::class)
        ->args([
            param('ekyna_product.class.bundle_choice'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.catalog_theme_choice', Catalog\CatalogThemeChoiceType::class)
        ->args([
            service('ekyna_product.registry.catalog'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.catalog_page', Catalog\CatalogPageType::class)
        ->args([
            service('ekyna_product.registry.catalog'),
        ])
        ->tag('form.type')
        ->tag('form.js', [
            'selector' => '.catalog-page',
            'path'     => 'ekyna-product/form/catalog-page',
        ]);

    $services
        ->set('ekyna_product.form_type.catalog_template_choice', Catalog\Template\TemplateChoiceType::class)
        ->args([
            service('ekyna_product.registry.catalog'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.sale_item.configurable_slots', SaleItem\ConfigurableSlotsType::class)
        ->args([
            service('ekyna_product.commerce.builder.item'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.sale_item.configurable_slot', SaleItem\ConfigurableSlotType::class)
        ->args([
            service('ekyna_product.commerce.builder.item'),
            service('ekyna_product.commerce.builder.form'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.sale_item.option_groups', SaleItem\OptionGroupsType::class)
        ->args([
            service('ekyna_product.commerce.builder.item'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.sale_item.option_group', SaleItem\OptionGroupType::class)
        ->args([
            service('ekyna_product.commerce.builder.item'),
            service('ekyna_product.commerce.builder.form'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.sale_item.variant_choice', SaleItem\VariantChoiceType::class)
        ->args([
            service('ekyna_product.commerce.builder.item'),
            service('ekyna_product.commerce.builder.form'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type_extension.sale_item_configure', SaleItemConfigureTypeExtension::class)
        ->args([
            service('ekyna_product.commerce.builder.form'),
            service('twig.form.renderer'),
            abstract_arg('Sale item form template'),
        ])
        ->tag('form.type_extension')
        ->tag('form.js', [
            'selector' => '.sale-item-configure',
            'path'     => 'ekyna-product/form/sale-item-configure',
        ]);

    $services
        ->set('ekyna_product.form_type.option', Option\OptionType::class)
        ->tag('form.type')
        ->tag('form.js', [
            'selector' => '.product-option',
            'path'     => 'ekyna-product/form/product-option',
        ]);

    $services
        ->set('ekyna_product.form_type.product', ProductType::class)
        ->args([
            service('ekyna_product.listener.product_form'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.product_adjustment', ProductAdjustmentType::class)
        ->args([
            service('translator'),
            abstract_arg('The adjustment designation choices.'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.product_attributes', ProductAttributesType::class)
        ->args([
            service('ekyna_product.registry.attribute_type'),
            param('ekyna_product.class.product_attribute'),
        ])
        ->tag('form.type')
        ->tag('form.js', [
            'selector' => '.product-attributes',
            'path'     => 'ekyna-product/form/product-attributes',
        ]);

    $services
        ->set('ekyna_product.form_type.attribute_boolean_config', Attribute\Config\BooleanConfigType::class)
        ->args([
            param('ekyna_resource.locales'),
            param('kernel.default_locale'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.editor_product_selection', Editor\ProductSelectionType::class)
        ->args([
            service('ekyna_product.repository.product'),
        ])
        ->tag('form.type');

    $services
        ->set('ekyna_product.form_type.convert_variable', VariableType::class)
        ->args([
            service('ekyna_product.repository.attribute_set'),
        ])
        ->tag('form.type');
};

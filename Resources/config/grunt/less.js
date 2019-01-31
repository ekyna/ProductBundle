module.exports = function (grunt, options) {
    // @see https://github.com/gruntjs/grunt-contrib-less
    return {
        product: {
            files: {
                'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/css/form.css':
                    'src/Ekyna/Bundle/ProductBundle/Resources/private/less/form.less',

                'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/css/admin/show.css':
                    'src/Ekyna/Bundle/ProductBundle/Resources/private/less/admin/show.less',

                'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/css/admin/highlight.css':
                    'src/Ekyna/Bundle/ProductBundle/Resources/private/less/admin/highlight.less',

                'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/css/admin/inventory.css':
                    'src/Ekyna/Bundle/ProductBundle/Resources/private/less/admin/inventory.less',

                'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/css/catalog/base.css':
                    'src/Ekyna/Bundle/ProductBundle/Resources/private/less/catalog/base.less',
                'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/css/catalog/default-theme.css':
                    'src/Ekyna/Bundle/ProductBundle/Resources/private/less/catalog/default-theme.less',

                'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/css/cms/product-slide.css':
                    'src/Ekyna/Bundle/ProductBundle/Resources/private/less/cms/product-slide.less'
            }
        }
    };
};

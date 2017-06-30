module.exports = function (grunt, options) {
    return {
        product: {
            options: {
                amd_wrapper: true,
                amd_define: 'ekyna-product/templates',
                variable: 'templates',
                template_key: function(path) {
                    var split = path.split('/');
                    return split[split.length-1];
                }
            },
            files: {
                'src/Ekyna/Bundle/ProductBundle/Resources/public/js/templates.js': [
                    'src/Ekyna/Bundle/ProductBundle/Resources/views/Js/sale_item_option_group.html.twig',
                    'src/Ekyna/Bundle/ProductBundle/Resources/views/Js/sale_item_pricing.html.twig',
                    'src/Ekyna/Bundle/ProductBundle/Resources/views/Js/inventory_line.html.twig'
                ]
            }
        }
    }
};

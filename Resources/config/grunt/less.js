module.exports = function (grunt, options) {
    // @see https://github.com/gruntjs/grunt-contrib-less
    return {
        product: {
            files: {
                'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/css/form.css':
                    'src/Ekyna/Bundle/ProductBundle/Resources/private/less/form.less',
                'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/css/show.css':
                    'src/Ekyna/Bundle/ProductBundle/Resources/private/less/show.less'
            }
        }
    }
};

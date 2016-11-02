module.exports = function (grunt, options) {
    return {
        product_ts: {
            files: [{
                expand: true,
                cwd: 'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/js',
                src: '**/*.js',
                dest: 'src/Ekyna/Bundle/ProductBundle/Resources/public/js'
            }]
        },
        product_js: {
            files: [{
                expand: true,
                cwd: 'src/Ekyna/Bundle/ProductBundle/Resources/private/js',
                src: '**/*.js',
                dest: 'src/Ekyna/Bundle/ProductBundle/Resources/public/js'
            }]
        }
    }
};

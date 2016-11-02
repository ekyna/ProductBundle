module.exports = function (grunt, options) {
    return {
        product_img: {
            files: [
                {
                    expand: true,
                    cwd: 'src/Ekyna/Bundle/ProductBundle/Resources/private/img',
                    src: ['**'],
                    dest: 'src/Ekyna/Bundle/ProductBundle/Resources/public/img'
                }
            ]
        },
        product_less: { // For watch:product_less
            files: [
                {
                    expand: true,
                    cwd: 'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/css',
                    src: ['**'],
                    dest: 'src/Ekyna/Bundle/ProductBundle/Resources/public/css'
                }
            ]
        },
        product_ts: { // For watch:product_ts
            files: [
                {
                    expand: true,
                    cwd: 'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/js',
                    src: ['**/*.js'],
                    dest: 'src/Ekyna/Bundle/ProductBundle/Resources/public/js'
                }
            ]
        },
        product_js: { // For watch:product_js
            files: [
                {
                    expand: true,
                    cwd: 'src/Ekyna/Bundle/ProductBundle/Resources/private/js',
                    src: ['**/*.js'],
                    dest: 'src/Ekyna/Bundle/ProductBundle/Resources/public/js'
                }
            ]
        }
    }
};

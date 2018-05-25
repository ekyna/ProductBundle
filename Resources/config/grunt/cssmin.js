module.exports = function (grunt, options) {
    return {
        product_less: {
            files: [
                {
                    expand: true,
                    cwd: 'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/css',
                    src: ['**/*.css'],
                    dest: 'src/Ekyna/Bundle/ProductBundle/Resources/public/css',
                    ext: '.css'
                }
            ]
        }
    }
};

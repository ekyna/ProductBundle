module.exports = function (grunt, options) {
    return {
        product: {
            files: [
                {
                    src: 'src/Ekyna/Bundle/ProductBundle/Resources/private/ts/**/*.ts',
                    dest: 'src/Ekyna/Bundle/ProductBundle/Resources/public/tmp/js'
                }
            ],
            options: {
                fast: 'never',
                module: 'amd',
                rootDir: 'src/Ekyna/Bundle/ProductBundle/Resources/private/ts',
                noImplicitAny: false,
                removeComments: true,
                preserveConstEnums: true,
                sourceMap: false
            }
        }
    }
};

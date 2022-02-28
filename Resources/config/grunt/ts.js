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
                rootDir: 'src/Ekyna/Bundle/ProductBundle/Resources/private/ts',
                //verbose: true,
                lib: ['dom', 'es2015', 'esnext'],
                target: 'es5',
                module: 'amd',
                moduleResolution: 'classic',
                noImplicitAny: false,
                removeComments: true,
                preserveConstEnums: true,
                sourceMap: false
            }
        }
    }
};

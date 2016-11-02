module.exports = function (grunt, options) {
    return {
        product_less: {
            files: ['src/Ekyna/Bundle/ProductBundle/Resources/private/less/**/*.less'],
            tasks: ['less:product', 'copy:product_less', 'clean:product_less'],
            options: {
                spawn: false
            }
        },
        product_js: {
            files: ['src/Ekyna/Bundle/ProductBundle/Resources/private/js/**/*.js'],
            tasks: ['copy:product_js'],
            options: {
                spawn: false
            }
        },
        product_ts: {
            files: ['src/Ekyna/Bundle/ProductBundle/Resources/private/ts/**/*.ts'],
            tasks: ['ts:product', 'copy:product_ts', 'clean:product_ts'],
            options: {
                spawn: false
            }
        }
    }
};

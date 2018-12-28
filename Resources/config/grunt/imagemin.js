module.exports = function (grunt, options) {
    return {
        product: {
            options: {
                optimizationLevel: 6
            },
            files: [{
                expand: true,
                cwd: 'src/Ekyna/Bundle/ProductBundle/Resources/private/img/',
                src: ['**/*.{png,jpg,gif,svg}'],
                dest: 'src/Ekyna/Bundle/ProductBundle/Resources/public/img/'
            }]
        }
    }
};
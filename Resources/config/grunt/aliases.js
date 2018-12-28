module.exports = {
    'build:product_css': [
        'less:product',
        'cssmin:product_less',
        'clean:product_less'
    ],
    'build:product_js': [
       'ts:product',
       'uglify:product_ts',
       'twig:product',
       'uglify:product_js',
       'clean:product_ts'
    ],
    'build:product': [
        'clean:product_pre',
        'imagemin:product',
        'build:product_css',
        'build:product_js',
        'clean:product_post'
    ]
};

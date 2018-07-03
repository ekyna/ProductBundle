define(['require', 'jquery'], function(require, $) {
    "use strict";

    var EkynaProduct = function() {};

    EkynaProduct.prototype = {
        init: function() {
            // Product slides
            var $slides = $('.product-slide');
            if (0 < $slides.length) {
                require(['ekyna-product/cms/product-slide'], function(ProductSlide) {
                    ProductSlide.init($slides);
                })
            }
        }
    };

    return new EkynaProduct;
});

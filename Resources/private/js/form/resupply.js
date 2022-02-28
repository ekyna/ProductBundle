define(['jquery'], function($) {
    "use strict";

    /**
     * Resupply widget
     */
    $.fn.resupplyWidget = function() {

        this.each(function() {

            var $form = $(this),
                $netPrice = $form.find('.resupply-net-price'),
                $quantity = $form.find('.resupply-quantity');
                //$eda = $form.find('.resupply-net-price');


            $form.on('change', 'input[name="supplierProduct"]', function(e) {
                $form.find('.supplier-product-details').hide();
                $form.find('.supplier-order-details').hide();

                var $checked = $form.find('input[name="supplierProduct"]:checked');
                if (1 === $checked.length) {
                    $netPrice.val($checked.data('price'));
                    $quantity.focus();
                }

                var value = $(e.currentTarget).val();
                $('#supplier_product_' + value + '_details')
                    .show()
                    .find('input[name="supplierOrder"]')
                    .first().prop('checked', true).trigger('change');
            });

            $form.on('change', 'input[name="supplierOrder"]', function(e) {
                $form.find('.supplier-order-details').hide();

                var value = $(e.currentTarget).val();
                $('#supplier_order_' + value + '_details').show();
            });
        });

        return this;
    };

    return {
        init: function($element) {
            $element.resupplyWidget();
        }
    };
});

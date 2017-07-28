define(['jquery'], function($) {
    "use strict";

    /**
     * Resupply widget
     */
    $.fn.resupplyWidget = function() {

        this.each(function() {

            var $form = $(this);

            $form.on('change', 'input[name="supplierProduct"]', function(e) {
                $form.find('.supplier-product-details').hide();
                $form.find('.supplier-order-details').hide();

                var value = $(e.currentTarget).val();
                $('#supplier_product_' + value + '_details')
                    .show()
                    .find('input[name="supplierOrder"]').first().prop('checked', true);
            });

            /*$form.on('change', 'input[name="supplierOrder"]', function(e) {
                $(e.currentTarget)
                    .closest('table.supplier-orders')
                    .find('.supplier-order-details').hide();

                var value = $(e.currentTarget).val();
                $('#supplier_order_' + value + '_details').show();
            });*/
        });

        return this;
    };

    return {
        init: function($element) {
            $element.resupplyWidget();
        }
    };
});

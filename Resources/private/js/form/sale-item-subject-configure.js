define(['jquery', 'ekyna-number'], function($) {
    "use strict";

    $.fn.saleItemSubjectConfigureWidget = function() {

        this.each(function() {

            var $form = $(this),
                $options = $form.find('select.sale-item-option'),
                $quantity = $form.find('input.sale-item-quantity'),
                $toggle = $form.find('button.sale-item-toggle-pricing'),
                $pricing = $form.find('table.sale-item-pricing'),
                $price = $form.find('span.sale-item-price'),
                pricing = $price.data('pricing');

            if (!(pricing.hasOwnProperty('rules') && pricing.hasOwnProperty('net_price') && pricing.hasOwnProperty('currency'))) {
                console.log('Invalid pricing data.');
                return;
            }

            $toggle.on('click', function() {
                if ($pricing.is(':visible')) {
                    $pricing.hide();
                } else {
                    $pricing.show();
                }
            });

            var basePrice = parseFloat(pricing.net_price);

            function buildPricingTable() {
                $pricing.find('tr').each(function() {
                    var price = basePrice * (100 - $(this).data('percent')) / 100;
                    $(this).find('td:last-child').html(price.formatPrice(pricing.currency));
                });
            }

            function resolvePrice() {
                $pricing.find('tr').removeClass('success');
                var quantity = parseFloat($quantity.val() || 1);
                var price = basePrice * quantity;
                $.each(pricing.rules, function(index, rule) {
                    if (quantity >= rule.quantity) {
                        price *= (100 - rule.percent) / 100;
                        $pricing.find('tr[data-quantity="' + rule.quantity + '"]').addClass('success');
                        return false;
                    }
                });
                $price.html(price.formatPrice(pricing.currency));
            }

            function calculateBasePrice() {
                basePrice = parseFloat(pricing.net_price);

                $options.each(function() {
                    var value = $(this).val();
                    if (0 <= value) {
                        var $option = $(this).find('option[value="' + value + '"]');
                        if (1 === $option.length && 0 < $option.data('price')) {
                            basePrice += parseFloat($option.data('price'));
                        }
                    }
                });

                buildPricingTable();
                resolvePrice();
            }

            $options.on('change', calculateBasePrice);
            $quantity.on('keyup mouseup change', resolvePrice);

            calculateBasePrice();
        });

        return this;
    };

    return {
        init: function($element) {
            $element.saleItemSubjectConfigureWidget();
        }
    };
});

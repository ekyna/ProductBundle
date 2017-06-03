define(['jquery', 'ekyna-number'], function($) {
    "use strict";

    $.fn.saleItemSubjectConfigureWidget = function() {

        this.each(function() {

            var $form = $(this),
                $options = $form.find('select.sale-item-option'),
                $quantity = $form.find('input.sale-item-quantity'),
                $priceTotal = $form.find('p.sale-item-price-total'),
                $priceHelp = $form.find('em.sale-item-price-help'),
                $rules = $form.find('table.sale-item-rules'),

                data = $form.find('div.sale-item-pricing').data('pricing'),
                quantity = parseFloat($quantity.val() || 1),
                activeRule = null;

            if (!(data.hasOwnProperty('rules') && data.hasOwnProperty('net_price') && data.hasOwnProperty('currency'))) {
                console.log('Invalid pricing data.');
                return;
            }

            var basePrice = parseFloat(data.net_price);

            function resolveRule() {
                if (1 === $rules.length) {
                    $rules.find('> tbody > tr').removeClass('success');
                    $.each(data.rules, function (index, rule) {
                        if (quantity >= parseFloat(rule.quantity)) {
                            activeRule = rule;
                            $rules.find('> tbody > tr[data-quantity="' + rule.quantity + '"]').addClass('success');
                            return false;
                        }
                    });
                }
            }

            function calculateTotalPrice() {
                var price = basePrice * quantity;
                if (null !== activeRule) {
                    price *= (100 - parseFloat(activeRule.percent)) / 100;
                }
                $priceTotal.html(price.formatPrice(data.currency));
            }

            function updatePricingTable() {
                $rules.find('tr').each(function() {
                    var price = basePrice * (100 - $(this).data('percent')) / 100;
                    $(this).find('td:last-child').html(price.formatPrice(data.currency));
                });
            }

            function onQuantityChange() {
                quantity = parseFloat($quantity.val() || 1);
                resolveRule();
                calculateTotalPrice();
                updateHelpText();
            }

            function updateHelpText() {
                var $sumParts = [];

                $options.each(function() {
                    var value = $(this).val();
                    if (0 <= value) {
                        var $option = $(this).find('option[value="' + value + '"]');
                        if (1 === $option.length && 0 < $option.data('price')) {
                            $sumParts.push(parseFloat($option.data('price')).toLocaleString());
                        }
                    }
                });

                var help = '';
                if (0 < $sumParts.length) {
                    $sumParts.unshift(parseFloat(data.net_price).toLocaleString());
                    help += '<br>' + $sumParts.join(' + ') + ' = ';
                }
                help += basePrice.formatPrice(data.currency);
                if (null !== activeRule) {
                    if (null !== activeRule) {
                        help += '<br>- ' + parseFloat(activeRule.percent).toLocaleString() + '% = '
                            + (basePrice * (100 - activeRule.percent) / 100).formatPrice(data.currency);
                    }
                }

                $priceHelp.html(help);
            }


            function onOptionsChange() {
                basePrice = parseFloat(data.net_price);

                $options.each(function() {
                    var value = $(this).val();
                    if (0 <= value) {
                        var $option = $(this).find('option[value="' + value + '"]');
                        if (1 === $option.length && 0 < $option.data('price')) {
                            basePrice += parseFloat($option.data('price'));
                        }
                    }
                });

                updatePricingTable();
                calculateTotalPrice();
                updateHelpText();
            }

            $form.find('div.sale-item-price').show();
            $rules.show();

            $options.on('change', onOptionsChange);
            $quantity.on('keyup mouseup change', onQuantityChange);

            resolveRule();
            onOptionsChange();

            // TODO On user login => load pricing data
        });

        return this;
    };

    return {
        init: function($element) {
            $element.saleItemSubjectConfigureWidget();
        }
    };
});

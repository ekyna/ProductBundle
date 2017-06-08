define(['jquery', 'ekyna-product/templates', 'ekyna-number'], function($, Templates) {
    "use strict";

    // -------------------------------- OPTION GROUP --------------------------------

    function OptionGroup($element, $parent) {
        this.$element = $element;

        if (undefined === $parent || $parent.size() === 0) {
            throw "Invalid '$groups' argument";
        }
        this.$parent = $parent;

        this.init();
    }

    $.extend(OptionGroup.prototype, {
        init: function() {
            this.$select = this.$element.find('select');

            this.bindEvents();
        },

        bindEvents: function() {
            var that = this;
            this.$select.on('change', function() {
                that.$parent.trigger('change');
            });
        },

        unbindEvents: function() {
            this.$select.off('change');
        },

        destroy: function() {
            this.unbindEvents();
            this.$element.removeData();
        },

        getId: function() {
            return this.$element.data('id');
        },

        getType: function() {
            return this.$element.data('type');
        },

        getPrice: function() {
            var $option = this.$select.find('option[value="' + this.$select.val() + '"]');
            if (1 === $option.length && 0 < $option.data('price')) {
                return parseFloat($option.data('price'));
            }
            return 0;
        }
    });

    $.fn.optionGroup = function($parent) {
        return this.each(function() {
            if (undefined === $(this).data('optionGroup')) {
                $(this).data('optionGroup', new OptionGroup($(this), $parent));
            }
        });
    };



    // -------------------------------- OPTION GROUPS --------------------------------

    function OptionGroups($element) {
        this.name = $element.attr('name');
        this.$element = $element;

        this.init();
    }

    $.extend(OptionGroups.prototype, {
        init: function() {
            this.loadGroups();
            this.bindEvents();
        },

        loadGroups: function() {
            this.$groups = this.$element.find(' > .form-group').optionGroup(this.$element);
        },

        bindEvents: function() {

        },

        unbindEvents: function() {

        },

        destroy: function() {
            this.unbindEvents();

            this.$groups.each(function() {
                $(this).data('optionGroup').destroy();
            });

            this.$element.removeData();
        },

        getPrice: function() {
            var price = 0;

            this.$groups.each(function() {
                price += $(this).data('optionGroup').getPrice();
            });

            return price;
        },

        create: function(data) {
            if (!data.hasOwnProperty('id')) {
                return;
            }

            if (0 === this.$groups.filter('[data-id="' +  data.id + '"]')) {
                return;
            }

            data.parent = this.name;

            $(Templates['sale_item_option_group.html.twig'].render(data))
                .appendTo(this.$element).optionGroup(this.$element);

            this.loadGroups();
        },

        removeByType: function(type) {
            this.$groups
                .filter('[data-type="' +  type + '"]')
                .each(function() {
                    $(this).data('optionGroup').destroy();
                })
                .remove();

            this.loadGroups();
        }
    });

    $.fn.optionGroups = function() {
        return this.each(function() {
            if (undefined === $(this).data('optionGroups')) {
                $(this).data('optionGroups', new OptionGroups($(this)));
            }
        });
    };



    // -------------------------------- VARIANT --------------------------------

    function Variant($element) {
        this.$element = $element;

        this.init();
    }

    $.extend(Variant.prototype, {
        init: function() {
            this.$variant = undefined;

            this.selectVariant();
            this.bindEvents();
        },

        bindEvents: function() {
            var that = this;
            this.$element.on('change', function() {
                that.selectVariant();
            });
        },

        unbindEvents: function() {
            this.$element.off('change');
        },

        destroy: function() {
            this.unbindEvents();
            this.$element.removeData();
        },

        selectVariant: function() {
            this.$variant = undefined;
            var $variant = this.$element.find('option[value="' + this.$element.val() + '"]');
            if (1 === $variant.size() && 0 < $variant.data('price')) {
                this.$variant = $variant;
            }
        },

        hasVariant: function() {
            return undefined !== this.$variant;
        },

        getPrice: function() {
            return this.$variant ? parseFloat(this.$variant.data('price')) : 0;
        },

        getOptionGroups: function() {
            return this.$variant ? this.$variant.data('optionGroups') : [];
        }
    });

    $.fn.variant = function() {
        return this.each(function() {
            if (undefined === $(this).data('variant')) {
                $(this).data('variant', new Variant($(this)));
            }
        });
    };



    // -------------------------------- SALE ITEM --------------------------------

    function SaleItem($element, $parent) {
        this.$element = $element;
        this.$parent = $parent;
        this.name = $element.attr('name');

        this.config = this.$element.data('config');

        this.init();
    }

    $.extend(SaleItem.prototype, {
        init: function() {
            this.$optionGroups = this.$element.find('#' + this.name + '_options').optionGroups(this.$element);

            this.$variant = this.$element.find('#' + this.name + '_variant');
            if (this.$variant.size() === 1) {
                this.$variant.variant();
            } else {
                this.$variant = undefined;
            }

            this.$quantity = this.$element.find('#' + this.name + '_quantity');

            this.$element.find('#' + this.name + '_pricing').show();
            this.$priceTotal = this.$element.find('#' + this.name + '_price_total');
            this.$priceHelp = this.$element.find('#' + this.name + '_price_help');

            this.quantity = 1;
            this.activeRule = undefined;
            this.basePrice = 0;
            this.unitPrice = 0;
            this.totalPrice = 0;

            this.bindEvents();

            this.onChange();
        },

        bindEvents: function() {
            var that = this;

            if (this.$variant) {
                this.$variant.on('change', function() {
                    that.onVariantChange();
                });
            }

            this.$optionGroups.on('change', function() {
                that.onChildChange();
            });

            this.$quantity.on('keyup change mouseup', function() {
                that.onQuantityChange();
            });
        },

        unbindEvents: function() {
            if (this.$variant) {
                this.$variant.variant().off('change');
            }

            this.$optionGroups.off('change');

            this.$quantity.off('keyup change mouseup');
        },

        destroy: function() {
            this.unbindEvents();

            if (this.$variant) {
                this.$variant.data('variant').destroy();
            }

            this.$optionGroups.data('optionGroups').destroy();

            this.$element.removeData();
        },

        onVariantChange: function() {
            var optionGroups = this.$optionGroups.data('optionGroups');
            optionGroups.removeByType('variant');

            var variant = this.$variant.data('variant');
            if (variant.hasVariant()) {
                $.each(variant.getOptionGroups(), function (index, data) {
                    optionGroups.create(data);
                });
            }

            this.onChildChange();
        },

        onChildChange: function() {
            if (this.calculatePrices()) {
                this.updatePriceHelp();
            }
        },

        onQuantityChange: function() {
            var quantity = this.$quantity.val();

            if (quantity !== this.quantity) {
                this.quantity = quantity;

                this.onChange();
            }
        },

        onChange: function() {
            var changed = this.resolveActiveRule();
            changed |= this.calculatePrices();

            if (changed) {
                this.updatePriceHelp();
            }
        },

        resolveActiveRule: function() {
            var activeRule;
            if (0 < this.config.rules.length) {
                $.each(this.config.rules, function(i, rule) {
                    if (this.quantity >= parseFloat(rule.quantity)) {
                        activeRule = rule;
                        return false;
                    }
                });
                if (activeRule !== this.activeRule) {
                    this.activeRule = activeRule;
                    return true;
                }
            }

            return false;
        },

        calculatePrices: function() {
            var basePrice, unitPrice, totalPrice;

            if (this.$variant) {
                basePrice = this.$variant.data('variant').getPrice();
            } else {
                basePrice = parseFloat(this.config.net_price);
            }

            basePrice += this.$optionGroups.data('optionGroups').getPrice();
            unitPrice = basePrice;
            if (this.activeRule) {
                unitPrice *= (100 - parseFloat(this.activeRule.percent)) / 100;
            }

            totalPrice = unitPrice * this.quantity;

            var changed = (basePrice !== this.basePrice || unitPrice !== this.unitPrice || totalPrice !== this.totalPrice);

            this.basePrice = basePrice;
            this.unitPrice = unitPrice;
            this.totalPrice = totalPrice;

            this.$priceTotal.html(this.totalPrice.formatPrice(this.config.currency));

            return changed;
        },

        updatePriceHelp: function() {
            if (0 === this.$priceHelp.size()) {
                return;
            }

            this.$priceHelp.html('<p>TODO</p>');
        },

        getUnitPrice: function() {
            return this.unitPrice;
        },

        getTotalPrice: function() {
            return this.totalPrice;
        }
    });

    $.fn.saleItem = function() {
        return this.each(function() {
            if (undefined === $(this).data('saleItem')) {
                $(this).data('saleItem', new SaleItem($(this)));
            }
        });
    };



    /*$.fn.saleItemWidget = function() {
        this.each(function() {

            var $form = $(this),
                config = $form.data('config');
                $options = $form.find('select.sale-item-option'),
                $quantity = $form.find('input.sale-item-quantity'),
                $priceTotal = $form.find('p.sale-item-price-total'),
                $priceHelp = $form.find('em.sale-item-price-help'),
                $rules = $form.find('table.sale-item-rules'),
                quantity = parseFloat($quantity.val() || 1),
                activeRule = null;

            var basePrice = parseFloat(config.pricing.net_price);

            function resolveRule() {
                if (1 === $rules.length) {
                    $rules.find('> tbody > tr').removeClass('success');
                    $.each(config.pricing.rules, function (index, rule) {
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
                $priceTotal.html(price.formatPrice(config.pricing.currency));
            }

            function updatePricingTable() {
                $rules.find('tr').each(function() {
                    var price = basePrice * (100 - $(this).data('percent')) / 100;
                    $(this).find('td:last-child').html(price.formatPrice(config.pricing.currency));
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
                    $sumParts.unshift(parseFloat(config.pricing.net_price).toLocaleString());
                    help += '<br>' + $sumParts.join(' + ') + ' = ';
                }
                help += basePrice.formatPrice(config.pricing.currency);
                if (null !== activeRule) {
                    if (null !== activeRule) {
                        help += '<br>- ' + parseFloat(activeRule.percent).toLocaleString() + '% = '
                            + (basePrice * (100 - activeRule.percent) / 100).formatPrice(config.pricing.currency);
                    }
                }

                $priceHelp.html(help);
            }


            function onOptionsChange() {
                basePrice = parseFloat(config.pricing.net_price);

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
    };*/

    return {
        init: function($element) {
            $element.saleItem();
        }
    };
});

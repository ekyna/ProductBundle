define(['jquery', 'ekyna-product/templates', 'ekyna-number'], function($, Templates) {
    "use strict";


    // -------------------------------- BUNDLE SLOT --------------------------------

    function BundleSlot($element) {
        this.$element = $element;
        this.id = $element.attr('id');

        this.init();
    }

    $.extend(BundleSlot.prototype, {
        init: function() {
            this.$radio = this.$element.find('.slot-buttons input[type=radio]');
            this.$choice = undefined;

            this.bindEvents();
            this.selectChoice();
        },

        bindEvents: function() {
            var that = this;
            this.$radio.on('change', function() {
                that.selectChoice();
            });
        },

        unbindEvents: function() {
            this.$radio.off('change');
        },

        destroy: function() {
            this.unbindEvents();
            this.$element.removeData();
            if (this.$choice) {
                this.$choice.data('saleItem').destroy();
            }
        },

        selectChoice: function() {
            var that = this,
                choiceId = this.$radio.filter(':checked').val(),
                $choice = this.$element.find('.slot-choice-form[data-id="' + choiceId + '"]');

            if (1 !== $choice.size()) {
                return;
            }

            var showChoice = function() {
                that.$choice = $choice
                    .prop('disabled', false)
                    .saleItem()
                    .fadeIn(250);

                that.$element.trigger('change');
            };

            if (this.$choice) {
                this.$choice
                    .fadeOut(250, function() {
                        that.$choice.data('saleItem').destroy();
                        that.$choice.prop('disabled', true);
                        showChoice();
                    });
            } else {
                showChoice();
            }
        },

        getPrice: function() {
            if (this.$choice) {
                this.$choice.data('saleItem').getTotalPrice();
            }

            return 0;
        }
    });

    $.fn.bundleSlot = function() {
        return this.each(function() {
            if (undefined === $(this).data('bundleSlot')) {
                $(this).data('bundleSlot', new BundleSlot($(this)));
            }
        });
    };


    // -------------------------------- OPTION GROUP --------------------------------

    function OptionGroup($element, $parent) {
        this.$element = $element;

        if (undefined === $parent || $parent.size() === 0) {
            throw "Invalid '$parent' argument";
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
            this.variantConfig = [];
            var $variant = this.$element.find('option[value="' + this.$element.val() + '"]');
            if (1 === $variant.size() && $variant.data('config')) {
                this.$variant = $variant;
                this.variantConfig = $.extend({
                    price: 0,
                    groups: []
                }, $variant.data('config'));
            }
        },

        hasVariant: function() {
            return undefined !== this.$variant;
        },

        getPrice: function() {
            return this.variantConfig ? parseFloat(this.variantConfig.price) : 0;
        },

        getOptionGroups: function() {
            return this.variantConfig ? this.variantConfig.groups : [];
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

    function SaleItem($element) {
        this.$element = $element;
        this.id = $element.attr('id');

        this.config = $.extend({
            net_price: 0,
            currency: 'EUR',
            rules: []
        }, this.$element.data('config'));

        this.init();
    }

    $.extend(SaleItem.prototype, {
        init: function() {
            this.$bundleSlots = this.$element.find('#' + this.id + '_configuration > .bundle-slot');
            console.log('bundle slots', this.$bundleSlots.size());
            if (0 < this.$bundleSlots.size()) {
                this.$bundleSlots.bundleSlot();
            }

            this.$optionGroups = this.$element.find('#' + this.id + '_options').optionGroups(this.$element);

            this.$variant = this.$element.find('#' + this.id + '_variant');
            if (this.$variant.size() === 1) {
                this.$variant.variant();
            } else {
                this.$variant = undefined;
            }

            this.$quantity = this.$element.find('#' + this.id + '_quantity');

            this.$element.find('#' + this.id + '_pricing').show();
            this.$priceTotal = this.$element.find('#' + this.id + '_price_total');
            this.$priceHelp = this.$element.find('#' + this.id + '_price_help');

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
            if (0 < this.config.rules.length) {
                var activeRule = undefined;
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

    return {
        init: function($element) {
            $element.saleItem();
        }
    };
});

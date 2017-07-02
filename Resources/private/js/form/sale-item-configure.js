define(['jquery', 'ekyna-product/templates', 'ekyna-number', 'fancybox'], function($, Templates) {
    "use strict";


    // -------------------------------- BUNDLE SLOT --------------------------------

    function BundleSlot($element, parentItem) {
        this.$element = $element;

        if (undefined === parentItem) {
            throw "Invalid 'parentItem' argument";
        }
        this.parentItem = parentItem;

        this.init();
    }

    $.extend(BundleSlot.prototype, {
        init: function() {
            this.id = this.$element.attr('id');
            this.busy = false;

            this.$choice = undefined;
            this.choice = undefined;

            this.$radio = this.$element.find('.slot-buttons input[type=radio]');
            this.$label = this.$element.find('.slot-buttons label');
            this.$prev = this.$element.find('.slot-choices > a.prev');
            this.$next = this.$element.find('.slot-choices > a.next');

            this.bindEvents();
            this.selectChoice();
        },

        bindEvents: function() {
            if (0 === this.$radio.size()) {
                return;
            }

            var that = this;

            this.$radio.on('change', function(e) {
                that.selectChoice();

                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            this.$label.on('click', function(e) {
                if (that.busy) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
            this.$prev.on('click', function(e) {
                if (that.busy) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }

                var index = that.$radio.filter(':checked').data('index') - 1,
                    $prev = that.$radio.filter('[data-index=' + index + ']');

                if (0 === $prev.size()) {
                    $prev = $(that.$radio.eq(that.$radio.size() - 1));
                }

                $prev.prop('checked', true);
                that.selectChoice();
            });
            this.$next.on('click', function(e) {
                if (that.busy) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }

                var index = that.$radio.filter(':checked').data('index') + 1,
                    $next = that.$radio.filter('[data-index=' + index + ']');

                if (0 === $next.size()) {
                    $next = $(that.$radio.eq(0));
                }

                $next.prop('checked', true);
                that.selectChoice();
            });
        },

        unbindEvents: function() {
            if (0 === this.$radio.size()) {
                return;
            }

            this.$radio.off('change');
            this.$label.off('click');
            this.$prev.off('click');
            this.$next.off('click');
        },

        destroy: function() {
            this.unbindEvents();
            this.$element.removeData();
            if (this.$choice) {
                this.$choice.data('saleItem').destroy();
            }
        },

        selectChoice: function() {
            if (0 === this.$radio.size()) {
                this.$choice = this.$element.find('.slot-choice-form').saleItem(this.parentItem);
                this.choice = this.$choice.data('saleItem');

                return;
            }

            var that = this,
                $current = that.$choice,
                choiceId = this.$radio.filter(':checked').val(),
                $selected = this.$element.find('.slot-choice-form[data-id="' + choiceId + '"]');

            if (1 !== $selected.size()) {
                return;
            }

            this.busy = true;

            var showChoice = function() {
                that.$choice = $selected;
                that.$choice.prop('disabled', false);
                that.$choice.saleItem(that.parentItem).fadeIn(250);
                that.choice = that.$choice.data('saleItem');

                that.$element.trigger('change');
                that.busy = false;
            };

            if ($current) {
                $current
                    .fadeOut(250, function() {
                        $current.data('saleItem').destroy();
                        $current.prop('disabled', true);
                        showChoice();
                    });
            } else {
                showChoice();
            }
        },

        updateQuantity: function() {
            if (this.choice) {
                this.choice.onQuantityChange();
            }
        },

        getChoice: function() {
            return this.choice
        },

        /*getPrice: function() {
            if (this.choice) {
                return this.choice.getTotalPrice();
            }

            return 0;
        },*/

        getLabel: function() {
            return this.$choice.find('.choice-title').html();
        }
    });

    $.fn.bundleSlot = function(parentItem) {
        return this.each(function() {
            if (undefined === $(this).data('bundleSlot')) {
                $(this).data('bundleSlot', new BundleSlot($(this), parentItem));
            }
        });
    };


    // -------------------------------- OPTION GROUP --------------------------------

    function OptionGroup($element, optionGroups) {
        this.$element = $element;

        if (!optionGroups) {
            throw "Invalid 'optionGroups' argument";
        }
        this.optionGroups = optionGroups;

        this.init();
    }

    $.extend(OptionGroup.prototype, {
        init: function() {
            this.$select = this.$element.find('select');
            this.$option = this.option = undefined;

            // hide if only Placeholder + Single option
            if (2 >= this.$select.children().length && this.$select.prop('required')) {
                this.$element.hide();
            }

            // Image
            this.$image = this.optionGroups.item.$gallery.find('a[data-option-id="' + this.$element.data('id') + '"]');
            if (0 === this.$image.size()) {
                this.$image = $('<a data-option-id="' + this.$element.data('id') + '"><img></a>');
                this.$image.appendTo(this.optionGroups.item.$gallery.find('.item-gallery-children'));
            }

            this.selectOption();
            this.bindEvents();
        },

        bindEvents: function() {
            var that = this;
            this.$select.on('change', function(e) {
                e.preventDefault();
                e.stopPropagation();

                that.selectOption();

                that.optionGroups.$element.trigger('change');

                return false;
            });
        },

        selectOption: function() {
            this.$option = this.option = undefined;

            var $option = this.$select.find('option[value="' + this.$select.val() + '"]');

            if (1 === $option.length && $option.data('config')) {
                this.$option = $option;
                this.option = $option.data('config');

                if (this.option.thumb) {
                    this.$image
                        .show()
                        .attr('href', this.option.image)
                        .attr('title', this.$option.text())
                        .find('img')
                        .attr('src', this.option.thumb);
                } else {
                    this.$image.hide();
                }
            } else {
                this.$image.hide();
            }
        },

        unbindEvents: function() {
            this.$select.off('change');
        },

        destroy: function() {
            this.unbindEvents();
            this.$image.remove();
            this.$element.removeData();
        },

        getId: function() {
            return this.$element.data('id');
        },

        getType: function() {
            return this.$element.data('type');
        },

        hasOption: function() {
            return !!this.$option;
        },

        getPrice: function() {
            return this.hasOption() ? parseFloat(this.option.price) : 0;
        },

        getLabel: function() {
            return this.hasOption() ? this.$option.text() : '';
        }
    });

    $.fn.optionGroup = function(optionGroups) {
        return this.each(function() {
            if (undefined === $(this).data('optionGroup')) {
                $(this).data('optionGroup', new OptionGroup($(this), optionGroups));
            }
        });
    };



    // -------------------------------- OPTION GROUPS --------------------------------

    function OptionGroups($element, item) {
        this.$element = $element;

        if (!item) {
            throw "Invalid 'item' argument";
        }
        this.item = item;

        this.init();
    }

    $.extend(OptionGroups.prototype, {
        init: function() {
            this.name = this.$element.attr('name');

            this.loadGroups();
            this.bindEvents();
        },

        loadGroups: function() {
            this.$groups = this.$element.find(' > .form-group').optionGroup(this);

            var groups = [];
            this.$groups.each(function() {
                groups.push($(this).data('optionGroup'));
            });
            this.groups = groups;
        },

        getGroups: function() {
            return this.groups;
        },

        hasOptions: function() {
            var hasOptions = false;
            $.each(this.groups, function() {
                hasOptions = this.hasOption();
                if (hasOptions) {
                    return false;
                }
            });
            return hasOptions;
        },

        bindEvents: function() {

        },

        unbindEvents: function() {

        },

        destroy: function() {
            this.unbindEvents();

            $.each(this.groups, function() {
                this.destroy();
            });

            this.$element.removeData();
        },

        hasGroups: function() {
            return 0 < this.groups.length();
        },

        getPrice: function() {
            var price = 0;

            $.each(this.groups, function() {
                price += this.getPrice();
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
                .appendTo(this.$element).optionGroup(this);

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

    $.fn.optionGroups = function(item) {
        return this.each(function() {
            if (undefined === $(this).data('optionGroups')) {
                $(this).data('optionGroups', new OptionGroups($(this), item));
            }
        });
    };



    // -------------------------------- VARIANT --------------------------------

    function Variant($element, item) {
        this.$element = $element;

        if (!item) {
            throw "Invalid 'item' argument";
        }
        this.item = item;

        this.init();
    }

    $.extend(Variant.prototype, {
        init: function() {
            // Image
            this.$image = this.item.$gallery.find('> a');
            if (1 === this.$image.size()) {
                // Default href and title
                this.$image
                    .data('href', this.$image.attr('href'))
                    .data('title', this.$image.attr('title'));
                // Default src
                var $img = this.$image.find('img');
                $img.data('src', $img.attr('src'));
            } else {
                this.$image = undefined;
            }

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
            if (this.$image) {
                this.$image.remove();
            }
            this.$element.removeData();
        },

        selectVariant: function() {
            this.$variant = undefined;
            this.variant = {
                label: '',
                price: 0,
                groups: []
            };

            var $variant = this.$element.find('option[value="' + this.$element.val() + '"]');
            if (1 === $variant.size() && $variant.data('config')) {
                this.$variant = $variant;
                this.variant = $.extend(this.variant, $variant.data('config'), {
                    label: $variant.text()
                });
                if (this.$image && this.variant.thumb) {
                    this.$image
                        .attr('href', this.variant.image)
                        .attr('title', this.variant.label)
                        .find('img')
                        .attr('src', this.variant.thumb);

                    return;
                }
            }

            if(this.$image) {
                this.$image
                    .attr('href', this.$image.data('href'))
                    .attr('title', this.$image.data('title'));

                var $img = this.$image.find('img');
                $img.attr('src', $img.data('src'));
            }
        },

        hasVariant: function() {
            return undefined !== this.$variant;
        },

        getPrice: function() {
            return parseFloat(this.variant.price);
        },

        getLabel: function() {
            return this.variant.label;
        },

        getOptionGroups: function() {
            return this.variant.groups;
        }
    });

    $.fn.variant = function(item) {
        return this.each(function() {
            if (undefined === $(this).data('variant')) {
                $(this).data('variant', new Variant($(this), item));
            }
        });
    };



    // -------------------------------- SALE ITEM --------------------------------

    function SaleItem($element, parentItem) {
        this.$element = $element;
        this.parentItem = parentItem;

        this.init();
    }

    $.extend(SaleItem.prototype, {
        init: function() {
            this.id = this.$element.attr('id');

            this.config = $.extend({
                price: 0,
                currency: 'EUR',
                rules: [],
                trans: {}
            }, this.$element.data('config'));


            // Images
            this.$gallery = undefined;
            var $gallery = $('#' + this.id + '_gallery');
            if (1 === $gallery.size()) {
                this.$gallery = $gallery.on('click', 'a', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var src = String($(this).attr('href'));
                    if (src.length) {
                        $.fancybox.open({
                            src: src,
                            caption: $(this).attr('title')
                        });
                    }

                    return false;
                });
            }

            // Quantity
            this.$quantity = this.$element.find('#' + this.id + '_quantity');
            this.quantity = this.$quantity.val();
            if (this.parentItem) {
                this.$parentQuantity = this.$quantity.parent().find('.sale-item-parent-qty');
                if (this.$parentQuantity.size() === 0) {
                    this.$parentQuantity = $('<span class="input-group-addon sale-item-parent-qty"></span>');
                    this.$parentQuantity.insertBefore(this.$quantity);
                }

                this.updateParentQuantity();
            }

            // Pricing
            this.$pricing = this.$element.find('#' + this.id + '_pricing');
            this.activeRule = undefined;
            this.basePrice = 0;
            this.unitPrice = 0;
            this.totalPrice = 0;

            // Variant
            this.$variant = this.variant = undefined;
            var $variant = this.$element.find('#' + this.id + '_variant');
            if ($variant.size() === 1) {
                this.$variant = $variant.variant(this);
                this.variant = $variant.data('variant');
            }

            // Bundle slots
            var bundleSlots = [];
            this.$bundleSlots = this.$element.find('#' + this.id + '_configuration > .bundle-slot');
            if (0 < this.$bundleSlots.size()) {
                this.$bundleSlots.bundleSlot(this).each(function() {
                    bundleSlots.push($(this).data('bundleSlot'));
                });
            }
            this.bundleSlots = bundleSlots;

            // Option groups
            this.optionGroups = undefined;
            this.$optionGroups = this.$element.find('#' + this.id + '_options').optionGroups(this);
            this.optionGroups = this.$optionGroups.data('optionGroups');

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

            this.$bundleSlots.on('change', function() {
                that.onChildChange();
            });

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

            this.$bundleSlots.off('change');

            this.$optionGroups.off('change');

            this.$quantity.off('keyup change mouseup');
        },

        destroy: function() {
            this.unbindEvents();

            if (this.variant) {
                this.variant.destroy();
            }

            $.each(this.bundleSlots, function() {
                this.destroy();
            });

            this.optionGroups.destroy();

            this.$element.removeData();
        },

        onVariantChange: function() {
            var that = this;

            this.optionGroups.removeByType('variant');

            if (this.variant && this.variant.hasVariant()) {
                $.each(this.variant.getOptionGroups(), function (index, data) {
                    that.optionGroups.create(data);
                });
            }

            this.onChildChange();
        },

        onChildChange: function() {
            this.calculatePrices();
            this.updatePricing();
        },

        updateParentQuantity: function() {
            if (this.parentItem) {
                if (1 < this.parentItem.getQuantity()) {
                    this.$parentQuantity.html(this.parentItem.getQuantity() + 'x').show();
                } else {
                    this.$parentQuantity.hide();
                }
            }
        },

        onQuantityChange: function() {
            this.quantity = this.$quantity.val();

            $.each(this.bundleSlots, function () {
                this.updateQuantity();
            });

            this.updateParentQuantity();

            this.onChange();
        },

        onChange: function() {
            this.resolveActiveRule();
            this.calculatePrices();
            this.updatePricing();
        },

        resolveActiveRule: function() {
            if (0 < this.config.rules.length) {
                var activeRule = undefined,
                    quantity = this.quantity;

                if (this.parentItem) {
                    quantity *= this.parentItem.getQuantity();
                }

                $.each(this.config.rules, function(i, rule) {
                    if (quantity >= parseFloat(rule.quantity)) {
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
            var basePrice = 0, unitPrice, totalPrice;

            if (0 < this.bundleSlots.length) {
                $.each(this.bundleSlots, function() {
                    basePrice += this.getChoice().getUnitPrice() * this.getChoice().getQuantity();
                });
            } else {
                if (this.variant) {
                    basePrice = this.variant.getPrice();
                } else {
                    basePrice = parseFloat(this.config.price);
                }
            }

            basePrice += this.optionGroups.getPrice();

            unitPrice = basePrice;
            if (this.activeRule) {
                unitPrice *= 1 - parseFloat(this.activeRule.percent) / 100;
            }

            totalPrice = unitPrice * this.quantity;
            if (this.parentItem) {
                totalPrice *= this.parentItem.getQuantity();
            }

            var changed = (basePrice !== this.basePrice || unitPrice !== this.unitPrice || totalPrice !== this.totalPrice);

            this.basePrice = basePrice;
            this.unitPrice = unitPrice;
            this.totalPrice = totalPrice;

            return changed;
        },

        updatePricing: function() {
            if (0 === this.$pricing.size()) {
                return;
            }

            var that = this, lines = [], rules = [], trans = this.config.trans;
            if (this.parentItem) {
                trans = $.extend(trans, this.parentItem.getConfig().trans);
            }

            var data = {
                detailed: false,
                trans: trans,
                basePrice: this.basePrice.formatPrice(that.config.currency),
                unitPrice: this.unitPrice.formatPrice(that.config.currency),
                totalPrice: this.totalPrice.formatPrice(that.config.currency)
            };

            // Rules
            if (that.activeRule) {
                data.detailed = true;
                $(this.config.rules).each(function (i, rule) {
                    var percent = parseFloat(rule.percent),
                        price = that.basePrice * (1 - percent / 100);
                    rules.push({
                        label: rule.label,
                        f_percent: rule.percent.toLocaleString() + '%',
                        f_price: price.formatPrice(that.config.currency),
                        active: that.activeRule.id === rule.id
                    });
                });
            }
            data.rules = rules.reverse();

            // Lines
            $.each(this.bundleSlots, function() {
                var price = this.getChoice().getUnitPrice() * this.getChoice().getQuantity();
                lines.push({
                    label: this.getLabel(),
                    price: price.formatPrice(that.config.currency)
                });
            });
            if (this.optionGroups.hasOptions()) {
                if (0 === lines.length) {
                    if (this.variant) {
                        lines.push({
                            label: this.variant.getLabel(),
                            price: this.variant.getPrice().formatPrice(that.config.currency)
                        });
                    } else {
                        lines.push({
                            label: 'Base price', // TODO
                            price: this.config.price.formatPrice(that.config.currency)
                        });
                    }
                }
                $.each(this.optionGroups.getGroups(), function () {
                    if (this.hasOption()) {
                        lines.push({
                            label: this.getLabel(),
                            price: this.getPrice().formatPrice(that.config.currency)
                        });
                    }
                });
            }
            if (lines.length > 0) {
                data.detailed = true;
                if (this.activeRule) {
                    var percent = parseFloat(this.activeRule.percent),
                        price = that.basePrice * percent / 100;
                    lines.push({
                        label: this.config.trans.discount + ' ' + percent.toLocaleString() + '%',
                        price: '-' + price.formatPrice(that.config.currency)
                    });
                }
                lines.push({
                    label: this.config.trans.unit_price,
                    price: this.unitPrice.formatPrice(that.config.currency),
                    class: 'info'
                });
            }
            data.lines = lines;

            this.$pricing.html(Templates['sale_item_pricing.html.twig'].render(data));
        },

        getConfig: function() {
            return this.config;
        },

        getQuantity: function() {
            return this.quantity;
        },

        getUnitPrice: function() {
            return this.unitPrice;
        },

        getTotalPrice: function() {
            return this.totalPrice;
        }
    });

    $.fn.saleItem = function(parentItem) {
        return this.each(function() {
            if (undefined === $(this).data('saleItem')) {
                $(this).data('saleItem', new SaleItem($(this), parentItem));
            }
        });
    };

    return {
        init: function($element) {
            $element.saleItem();
        },
        destroy: function($element) {
            var saleItem = $element.data('saleItem');
            if (undefined !== saleItem) {
                saleItem.destroy();
            }
        }
    };
});

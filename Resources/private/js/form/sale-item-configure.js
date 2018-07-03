define(['require', 'jquery', 'ekyna-product/templates', 'ekyna-number'], function (require, $, Templates) {
    "use strict";

    var toggleDisabled = function ($element, disabled) {
        $element
            .prop('disabled', disabled)
            .find('input, select, button, textarea')
            .not('[data-locked="1"]')
            .prop('disabled', disabled);
    };

    /**
     * Pricing
     */
    var Pricing = {
        price: 0,
        discounts: [],
        taxes: []
    };

    Pricing.currency = 'USD';
    Pricing.mode = 'ati';

    /**
     * @param {number} [quantity = 1]
     * @param {boolean} [discounted = true]
     * @return {number}
     */
    Pricing.calculate = function (quantity, discounted) {
        if (typeof this.price !== 'number' || 0 > this.price) {
            return 0;
        }

        if (quantity === undefined || 1 > quantity) {
            quantity = 1;
        }
        if (discounted === undefined) {
            discounted = true;
        }

        var result = this.price,
            discount = undefined;

        if (discounted) {
            if (Array.isArray(this.discounts)) {
                discount = this.discounts.find(function (d) {
                    return d.quantity <= quantity;
                });
            }
            if (discount) {
                result -= this.price * discount.percent / 100;
            }
        }

        if (Array.isArray(this.taxes) && Pricing.mode === 'ati') {
            var base = result;
            this.taxes.forEach(function (rate) {
                result += base * rate / 100;
            });
        }

        return result;
    };

    /**
     * @param {number} [quantity = 1]
     * @returns {string}
     */
    Pricing.display = function (quantity) {
        var final = Pricing.calculate.call(this, quantity),
            original = Pricing.calculate.call(this, quantity, false),
            prefix = ''; //SaleItem.trans.unit_price + '&nbsp;';

        if (original !== final) {
            prefix = '<del>' + original.formatPrice(Pricing.currency) + '</del>&nbsp;';
        }

        return prefix +
            '<strong>' + final.formatPrice(Pricing.currency) + '</strong>&nbsp;' +
            '<sup>' + (Pricing.mode === 'ati' ? SaleItem.trans.ati : SaleItem.trans.net) + '</sup>';
    };

    /**
     * Availability result
     */
    function AvailabilityResult(level, message, qty) {
        this.level = level;
        this.message = message;
        this.qty = qty === undefined ? 0 : qty;
    }

    AvailabilityResult.prototype.getClass = function() {
        if (this.level === 3) {
            return 'error';
        }

        if (0 < this.level) {
            return 'warning';
        }

        return null;
    };

    AvailabilityResult.prototype.hasClass = function() {
        return 0 < this.level;
    };

    AvailabilityResult.prototype.display = function() {
        if (this.message && 0 < this.message.length) {
            return '<span>' + this.message + '</span>';
        }

        return '';
    };

    AvailabilityResult.prototype.merge = function(result) {
        if (!result) {
            return;
        }

        if (result.level > this.level || (0 === result.level && result.level === this.level && result.qty < this.qty)) {
            this.level = result.level;
            this.message = result.message;
            this.qty = result.qty;
        }
    };

    /**
     * Availability
     */
    var Availability = {
        o_msg: '',
        min_qty: 0,
        min_msg: '',
        max_qty: 0,
        max_msg: '',
        a_qty: 0,
        a_msg: '',
        r_qty: 0,
        r_msg: ''
    };

    /**
     * @param {number} [quantity = 1]
     * @returns {AvailabilityResult}
     */
    Availability.resolve = function (quantity) {
        if (quantity === undefined) {
            quantity = 1;
        }

        var max = this.max_qty === 'INF' ? Number.POSITIVE_INFINITY : this.max_qty;
        if (quantity > max) {
            return new AvailabilityResult(3, this.max_msg);
        }

        if (quantity < this.min_qty) {
            return new AvailabilityResult(3, this.min_msg);
        }

        if (quantity > this.a_qty) {
            this.class = 'warning';
            if (this.r_msg && this.quantity <= this.a_qty + this.r_qty) {
                return new AvailabilityResult(1, this.r_msg); // TODO EDA / days
            }

            return new AvailabilityResult(2, this.o_msg);
        }

        return new AvailabilityResult(0, this.a_msg, this.a_qty);
    };

    /*Availability.display = function (quantity) {
        var result = Availability.resolve.call(this, quantity);

        return result.message ? '<span>' + result.message + '</span>' : '';
    };*/

    /*Availability.merge = function(data, qty) {
        if (null === data) {
            return;
        }

        var max = data.max_qty === 'INF' ? Number.POSITIVE_INFINITY : data.max_qty;

        if (qty < data.min_qty) {
            if (!this.min_qty || this.min_qty < data.min_qty) {
                this.min_qty = data.min_qty;
                this.min_msg = data.min_msg;
            }
        } else if (qty > max) {
            if (!this.max_qty || this.max_qty > max) {
                this.max_qty = max;
                this.max_msg = data.max_msg;
            }
        } else if (qty > data.a_qty) {
            if (data.r_msg) {
                if (qty > data.a_qty + data.r_qty) {
                    this.o_msg = data.o_msg;
                } else if (!this.r_qty || this.r_qty > data.r_qty) {
                    this.r_qty = data.r_qty;
                    this.r_msg = data.r_msg;
                }
            } else {
                this.o_msg = data.o_msg;
            }
        } else if (!this.a_qty || this.a_qty > data.a_qty) {
            this.a_qty = data.a_qty;
            this.a_msg = data.a_msg;
        }
    };*/

    /*Availability.merge = function(data) {
        if (null === data) {
            return;
        }
        if (!this.hasOwnProperty('o_msg') && data.hasOwnProperty('o_msg')) {
            this.o_msg = data.o_msg;
        }
        var that = this;
        ['min', 'max', 'a', 'r'].forEach(function(key) {
            if (!data.hasOwnProperty(key + '_qty')) {
                return;
            }
            if (!that.hasOwnProperty(key + '_qty') || (data[key + '_qty'] !== 'INF' && that[key + '_qty'] > data[key + '_qty'])) {
                that[key + '_qty'] = data[key + '_qty'];
                that[key + '_msg'] = data[key + '_msg'];
            }
        });
    };*/

    // -------------------------------- BUNDLE SLOT --------------------------------

    function BundleSlot ($element, parentItem) {
        this.$element = $element;

        if (undefined === parentItem) {
            throw "Invalid 'parentItem' argument";
        }
        this.parentItem = parentItem;

        this.init();
    }

    $.extend(BundleSlot.prototype, {
        init: function () {
            this.busy = false;

            this.$choice = undefined;
            this.choice = undefined;

            this.$radio = this.$element.find('.slot-buttons input[type=radio]');
            this.$label = this.$element.find('.slot-buttons label');
            this.$prev = this.$element.find('.slot-choices > a.prev');
            this.$next = this.$element.find('.slot-choices > a.next');

            this.$element.find('.slot-choice-form[disabled]').each(function () {
                toggleDisabled($(this), true);
            });

            this.bindEvents();
            this.selectChoice();
        },

        bindEvents: function () {
            if (0 === this.$radio.size()) {
                return;
            }

            var that = this;

            this.$radio.on('change', function (e) {
                that.selectChoice();

                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            this.$label.on('click', function (e) {
                if (that.busy) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
            this.$prev.on('click', function (e) {
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
            this.$next.on('click', function (e) {
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

        unbindEvents: function () {
            if (0 === this.$radio.size()) {
                return;
            }

            this.$radio.off('change');
            this.$label.off('click');
            this.$prev.off('click');
            this.$next.off('click');
        },

        destroy: function () {
            this.unbindEvents();
            this.$element.removeData();
            if (this.$choice && this.$choice.data('saleItem')) {
                this.$choice.data('saleItem').destroy();
            }
        },

        selectChoice: function () {
            if (0 === this.$radio.size()) {
                this.$choice = this.$element.find('.slot-choice-form').saleItem(this.parentItem);
                this.choice = this.$choice.data('saleItem');

                return;
            }

            var that = this,
                $current = that.$choice,
                current = that.choice,
                choiceId = this.$radio.filter(':checked').val() || 0,
                $selected = this.$element.find('.slot-choice-form[data-id="' + choiceId + '"]');

            if (1 !== $selected.size()) {
                return;
            }

            this.busy = true;

            var showChoice = function () {
                toggleDisabled($selected, false);
                that.$choice = $selected;
                if (1 === that.$choice.find('input.sale-item-quantity').size()) {
                    that.$choice.saleItem(that.parentItem);
                    that.choice = that.$choice.data('saleItem');
                } else {
                    that.choice = undefined;
                }
                that.$choice.fadeIn(250);
                that.$element.trigger('change');
                that.busy = false;
            };

            if ($current) {
                $current
                    .fadeOut(250, function () {
                        if (current) {
                            current.destroy();
                        }
                        toggleDisabled($current, true);
                        showChoice();
                    });
            } else {
                showChoice();
            }
        },

        updateQuantity: function () {
            if (this.choice) {
                this.choice.onQuantityChange();
            }
        },

        hasChoice: function () {
            return undefined !== this.choice;
        },

        getChoice: function () {
            return this.choice;
        }
    });

    $.fn.bundleSlot = function (parentItem) {
        return this.each(function () {
            if (undefined === $(this).data('bundleSlot')) {
                $(this).data('bundleSlot', new BundleSlot($(this), parentItem));
            }
        });
    };


    // -------------------------------- OPTION GROUP --------------------------------

    function OptionGroup ($element, optionGroups) {
        this.$element = $element;

        if (!optionGroups) {
            throw "Invalid 'optionGroups' argument";
        }
        this.optionGroups = optionGroups;

        this.init();
    }

    $.extend(OptionGroup.prototype, {
        init: function () {
            this.$select = this.$element.find('select');
            this.$option = this.option = undefined;
            this.locked = !!this.$select.attr('data-locked');

            // Image
            this.$image = undefined;
            if (this.optionGroups.item.$gallery) {
                this.$image = this.optionGroups.item.$gallery.find('a[data-option-id="' + this.$element.data('id') + '"]');
                if (0 === this.$image.size()) {
                    this.$image = $('<a data-option-id="' + this.$element.data('id') + '"><img></a>');
                    this.$image.appendTo(this.optionGroups.item.$gallery.find('.item-gallery-children'));
                }
            }

            this.$info = this.$element.find('.sale-item-info');

            this.updateState();
            this.selectOption();
            this.bindEvents();
        },

        updateState: function () {
            // Disable if locked
            if (this.locked) {
                this.$select.prop('disabled', true);
                return;
            }

            // Disable if Placeholder + Single option
            /*if (this.$element.find('label').hasClass('required') && 1 >= this.$select.children().length) {
                this.$select.prop('disabled', true);
                return;
            }*/

            this.$select.prop('disabled', false);
        },

        bindEvents: function () {
            var that = this;
            this.$select.on('change', function (e) {
                e.preventDefault();
                e.stopPropagation();

                that.selectOption();

                that.optionGroups.$element.trigger('change');

                return false;
            });
        },

        selectOption: function () {
            this.$option = undefined;

            this.option = {
                label: null,
                thumb: null,
                image: null,
                pricing: null,
                availability: null
            };

            var $option = this.$select.find('option[value="' + this.$select.val() + '"]');
            if (1 === $option.length && $option.data('config')) {
                this.$option = $option;
                this.option = $.extend(this.option, $option.data('config'));

                if (this.$image) {
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
                }
            } else if (this.$image) {
                this.$image.hide();
            }

            this.updatePricesAndAvailability();
        },

        updatePricesAndAvailability: function () {
            this.$info.empty();
            this.$element.removeClass('has-error has-warning');

            if (!this.option) {
                return;
            }

            var quantity = this.getTotalQuantity(), prices = '', availability = '';

            if (this.option.availability) {
                // TODO this.availability
                var result = Availability.resolve.call(this.option.availability, quantity);
                if (result.hasClass()) {
                    this.$element.addClass('has-' + result.getClass());
                }
                availability = result.display();
            }
            if (this.option.pricing) {
                prices = Pricing.display.call(this.option.pricing, quantity);
            }

            this.$info.html(availability + prices);
        },

        getTotalQuantity: function() {
            // TODO option quantity
            return this.optionGroups.item.getTotalQuantity();
        },

        unbindEvents: function () {
            this.$select.off('change');
        },

        destroy: function () {
            this.unbindEvents();
            if (this.$image) {
                this.$image.remove();
            }
            this.$element.removeData();
        },

        getId: function () {
            return this.$element.data('id');
        },

        getType: function () {
            return this.$element.data('type');
        },

        hasOption: function () {
            return !this.locked && !!this.$option;
        },

        getAvailability: function() {
            if (this.hasOption()) {
                return this.option.availability;
            }

            return null;
        },

        getOriginalPrice: function() {
            if (this.hasOption()) {
                return Pricing.calculate.call(this.option.pricing, this.optionGroups.item.getTotalQuantity(), false);
            }

            return 0;
        },

        getFinalPrice: function() {
            if (this.hasOption()) {
                return Pricing.calculate.call(this.option.pricing, this.optionGroups.item.getTotalQuantity());
            }

            return 0;
        },

        isLocked: function () {
            return this.locked;
        },

        isRequired: function () {
            return this.$select.prop('required');
        },

        lock: function () {
            if (!this.locked) {
                this.$select.attr('data-locked', "1");
                this.locked = true;
            }

            return this;
        },

        unlock: function () {
            if (this.locked) {
                this.$select.attr('data-locked', "0");
                this.locked = false;
            }

            return this;
        },

        hide: function () {
            this.$element.hide();
            if (this.$image && this.$image.size()) {
                this.$image.hide();
            }

            return this;
        },

        show: function () {
            this.$element.show();
            if (this.$image && this.$image.size()) {
                this.$image.show();
            }

            return this;
        }
    });

    $.fn.optionGroup = function (optionGroups) {
        return this.each(function () {
            if (undefined === $(this).data('optionGroup')) {
                $(this).data('optionGroup', new OptionGroup($(this), optionGroups));
            }
        });
    };


    // -------------------------------- OPTION GROUPS --------------------------------

    function OptionGroups ($element, item) {
        this.$element = $element;

        if (!item) {
            throw "Invalid 'item' argument";
        }
        this.item = item;

        this.init();
    }

    $.extend(OptionGroups.prototype, {
        init: function () {
            this.name = this.$element.attr('name');

            this.loadGroups();
            this.bindEvents();
        },

        loadGroups: function () {
            var that = this;
            this.$groups = [];
            this.groups = [];

            this.$element.find(' > .form-group').each(function () {
                var $group = $(this).optionGroup(that);
                that.$groups.push($group);
                that.groups.push($group.data('optionGroup'));
            });
        },

        getGroups: function () {
            return this.groups;
        },

        hasOptions: function () {
            var hasOptions = false;
            $.each(this.groups, function () {
                hasOptions = this.hasOption();
                if (hasOptions) {
                    return false;
                }
            });
            return hasOptions;
        },

        bindEvents: function () {

        },

        unbindEvents: function () {

        },

        destroy: function () {
            this.unbindEvents();

            $.each(this.groups, function () {
                this.destroy();
            });

            this.$element.removeData();
        },

        hasGroups: function () {
            return 0 < this.groups.length;
        },

        getOriginalPrice: function() {
            var price = 0;

            $.each(this.groups, function () {
                if (!this.isLocked()) {
                    price += this.getOriginalPrice();
                }
            });

            return price;
        },

        getFinalPrice: function() {
            var price = 0;

            $.each(this.groups, function () {
                if (!this.isLocked()) {
                    price += this.getFinalPrice();
                }
            });

            return price;
        },

        // TODO remove
        getPrice: function () {
            var price = 0;

            $.each(this.groups, function () {
                if (!this.isLocked()) {
                    price += this.getPrice();
                }
            });

            return price;
        },

        create: function (data) {
            if (!data.hasOwnProperty('id')) {
                return;
            }

            var $group = this.$element.find('> .form-group[data-id="' + data.id + '"]');
            if (1 === $group.size()) {
                $group.data('optionGroup').show().unlock().updateState();
                return;
            }

            data.parent = this.name;

            $group = $(Templates['sale_item_option_group.html.twig'].render(data));
            $group.appendTo(this.$element).optionGroup(this);

            this.$groups.push($group);
            this.groups.push($group.data('optionGroup'));
        },

        lockByType: function (type) {
            this.$element.find('> .form-group[data-type="' + type + '"]')
                .each(function () {
                    $(this).data('optionGroup').lock().hide().updateState();
                });

            return this;
        },

        hideByType: function (type) {
            this.$element.find('> .form-group[data-type="' + type + '"]')
                .each(function () {
                    $(this).data('optionGroup').hide();
                });

            return this;
        },

        resolveAvailability: function() {
            var result = null;

            $.each(this.groups, function () {
                if (!this.isLocked() && this.hasOption()) {
                    var r = Availability.resolve.call(this.getAvailability(), this.getTotalQuantity());
                    if (!result) {
                        result = r;
                    } else {
                        result.merge(r);
                    }
                }
            });

            return result;
        },

        updatePricesAndAvailability: function () {
            $.each(this.groups, function () {
                this.updatePricesAndAvailability();
            });
        }
    });

    $.fn.optionGroups = function (item) {
        return this.each(function () {
            if (undefined === $(this).data('optionGroups')) {
                $(this).data('optionGroups', new OptionGroups($(this), item));
            }
        });
    };


    // -------------------------------- VARIANT --------------------------------

    function Variant ($element, item) {
        this.$element = $element;

        if (!item) {
            throw "Invalid 'item' argument";
        }
        this.item = item;

        this.init();
    }

    $.extend(Variant.prototype, {
        init: function () {
            // Image
            this.$image = undefined;
            if (this.item.$gallery) {
                this.$image = this.item.$gallery.find('> a');
                if (1 === this.$image.size()) {
                    // Default href and title
                    this.$image
                        .data('href', this.$image.attr('href'))
                        .data('title', this.$image.attr('title'));
                    // Default src
                    var $img = this.$image.find('img');
                    $img.data('src', $img.attr('src'));
                }
            }

            this.$info = this.$element.closest('.input-group').find('.sale-item-info');

            this.selectVariant();
            this.bindEvents();
        },

        bindEvents: function () {
            var that = this;
            this.$element.on('change', function () {
                that.selectVariant();
            });
        },

        unbindEvents: function () {
            this.$element.off('change');
        },

        destroy: function () {
            this.unbindEvents();
            this.$element.removeData();
        },

        selectVariant: function () {
            this.$variant = undefined;
            this.variant = { // TODO rename config to variant (+getter)
                label: null,
                thumb: null,
                image: null,
                pricing: null,
                groups: [],
                availability: null
            };

            var $variant = this.$element.find('option[value="' + this.$element.val() + '"]').eq(0);
            if (1 === $variant.size() && $variant.data('config')) {
                this.$variant = $variant;

                $.extend(this.variant, $variant.data('config'), {
                    label: $variant.text()
                });
            }

            this.updatePricesAndAvailability();

            if (this.$image) {
                if (this.variant.thumb) {
                    this.$image
                        .attr('href', this.variant.image)
                        .attr('title', this.variant.label)
                        .find('img')
                        .attr('src', this.variant.thumb);

                    return;
                }

                this.$image
                    .attr('href', this.$image.data('href'))
                    .attr('title', this.$image.data('title'));

                var $img = this.$image.find('img');
                $img.attr('src', $img.data('src'));
            }
        },

        updatePricesAndAvailability: function () {
            this.$info.empty();
            this.$element.closest('.form-group').removeClass('has-error has-warning');

            if (!(this.variant)) {
                return;
            }

            var quantity = this.item.getTotalQuantity(),
                availability = Availability.resolve.call(this.variant.availability, quantity),
                prices = Pricing.display.call(this.variant.pricing, quantity);

            if (availability.hasClass()) {
                this.$element.closest('.form-group').addClass('has-' + availability.getClass());
            }

            this.$info.html(availability.display() + prices);
        },

        hasVariant: function () {
            return undefined !== this.$variant;
        },

        getVariant: function () {
            return this.variant;
        },

        getOptionGroups: function () {
            return this.variant.groups;
        }
    });

    $.fn.variant = function (item) {
        return this.each(function () {
            if (undefined === $(this).data('variant')) {
                $(this).data('variant', new Variant($(this), item));
            }
        });
    };


    // -------------------------------- SALE ITEM --------------------------------

    function SaleItem ($element, parentItem) {
        this.$element = $element;
        this.parentItem = parentItem;

        this.availability = {};
        this.originalPrice = 0;
        this.finalPrice = 0;

        this.init();
    }

    SaleItem.trans = {
        quantity: 'Quantity',
        discount: 'Discount',
        unit_price: 'Unit price',
        total: 'Net total',
        rule_table: 'Your prices',
        price_table: 'Detailed unit price',
        ati: 'ATI',
        net: 'Net'
    };

    $.extend(SaleItem.prototype, {
        init: function () {
            this.id = this.$element.attr('id');

            this.config = $.extend({
                price: 0,
                pricing: {
                    price: 0,
                    discounts: [],
                    taxes: []
                },
                availability: null,
                privileged: false
            }, this.$element.data('config'));

            var globals = this.$element.data('globals');
            if (globals) {
                Pricing.mode = globals.mode;
                Pricing.currency = globals.currency;
            }

            var trans = this.$element.data('trans');
            if (trans) {
                SaleItem.trans = trans;
            }

            // Images
            this.$gallery = undefined;
            var $gallery = $('#' + this.id + '_gallery');
            if (1 === $gallery.size()) {
                this.$gallery = $gallery;
            }

            // Quantity
            this.$quantity = this.$element.find('#' + this.id + '_quantity');
            this.quantity = parseFloat(this.$quantity.val());
            this.totalQuantity = this.quantity;
            if (this.parentItem) {
                this.totalQuantity = this.quantity * this.parentItem.getTotalQuantity();

                this.$parentQuantity = this.$quantity.parent().find('.sale-item-parent-qty');
                if (this.$parentQuantity.size() === 0) {
                    this.$parentQuantity = $('<span class="input-group-addon sale-item-parent-qty"></span>');
                    this.$parentQuantity.insertBefore(this.$quantity);
                }
                this.updateParentQuantity();
            }

            // Pricing
            this.$pricing = this.$element.find('#' + this.id + '_pricing');

            // Option groups
            this.optionGroups = undefined;
            this.$optionGroups = this.$element.find('#' + this.id + '_options').optionGroups(this);
            this.optionGroups = this.$optionGroups.data('optionGroups');

            // Variant
            this.$variant = this.variant = undefined;
            var $variant = this.$element.find('#' + this.id + '_variant');
            if ($variant.size() === 1) {
                this.$variant = $variant.variant(this);
                this.variant = $variant.data('variant');

                this.createVariantOptionGroups();
            }

            // Bundle slots
            var bundleSlots = [];
            this.$bundleSlots = this.$element.find('#' + this.id + '_configuration > .bundle-slot');
            if (0 < this.$bundleSlots.size()) {
                this.$bundleSlots.bundleSlot(this).each(function () {
                    bundleSlots.push($(this).data('bundleSlot'));
                });
            }
            this.bundleSlots = bundleSlots;

            this.$availability = this.parentItem
                ? this.$element.find('.sale-item-availability')
                : this.$element.find('.sale-item-inner .sale-item-availability');

            // Submit button
            this.$submitButton = undefined;
            if (!this.parentItem) {
                this.$submitButton = this.$element.find('button[type=submit]');
                if (0 === this.$submitButton.size()) {
                    this.$submitButton = this.$element.closest('.modal-content').find('.bootstrap-dialog-footer button#submit');
                }
                if (0 === this.$submitButton.size()) {
                    throw 'Submit button not found';
                }
            }

            this.bindEvents();
            this.onChange();
        },

        bindEvents: function () {
            var that = this;

            if (this.$variant) {
                this.$variant.on('change', function () {
                    that.createVariantOptionGroups();
                    that.onChildChange();
                });
            }

            this.$bundleSlots.on('change', function () {
                that.onChildChange();
            });

            this.$optionGroups.on('change', function () {
                that.onChildChange();
            });

            this.$quantity.on('keyup change mouseup', function () {
                that.onQuantityChange();
            });
        },

        unbindEvents: function () {
            if (this.$variant) {
                this.$variant.variant().off('change');
            }

            this.$bundleSlots.off('change');

            this.$optionGroups.off('change');

            this.$quantity.off('keyup change mouseup');
        },

        destroy: function () {
            this.unbindEvents();

            if (this.variant) {
                this.variant.destroy();
            }

            $.each(this.bundleSlots, function () {
                this.destroy();
            });

            this.optionGroups.destroy();

            this.$element.removeData();
        },

        createVariantOptionGroups: function () {
            var that = this;

            // TODO we're losing initial selection T_T
            this.optionGroups.lockByType('variant').hideByType('variant');

            // Lock by type (to prevent enable on show choice)

            if (this.variant && this.variant.hasVariant()) {
                $.each(this.variant.getOptionGroups(), function (index, data) {
                    that.optionGroups.create(data);
                });
            }

            // Sort option groups
            // -> required/variable first
            var $groups = this.optionGroups.$element.find('.form-group');
            $groups.sort(function (a, b) {
                var aGroup = $(a).data('optionGroup'),
                    bGroup = $(b).data('optionGroup'),
                    aRequired = aGroup.isRequired(),
                    bRequired = bGroup.isRequired();

                if (aRequired && bRequired) {
                    var aType = aGroup.getType(),
                        bType = bGroup.getType();
                    if (aType === 'variant' && bType === 'variant') {
                        return 0;
                    } else if (aType === 'variant' && bType !== 'variant') {
                        return 1;
                    }
                } else if (!aRequired && bRequired) {
                    return 1;
                }

                return -1;
            });
            $groups.detach().appendTo(this.optionGroups.$element);
        },

        updateParentQuantity: function () {
            if (1 < this.parentItem.getQuantity()) {
                this.$parentQuantity.show().html(this.parentItem.getQuantity() + 'x');
            } else {
                this.$parentQuantity.hide();
            }
        },

        onQuantityChange: function () {
            this.quantity = parseFloat(this.$quantity.val());
            this.totalQuantity = this.quantity;

            if (this.parentItem) {
                this.updateParentQuantity();
                this.totalQuantity = this.quantity * this.parentItem.getTotalQuantity();
            }

            if (this.variant) {
                this.variant.updatePricesAndAvailability();
            }

            this.optionGroups.updatePricesAndAvailability();

            $.each(this.bundleSlots, function () {
                this.updateQuantity();
            });

            this.onChange();
        },

        onChildChange: function () {
            this.updatePricesAndAvailability();
        },

        onChange: function () {
            this.updatePricesAndAvailability();
        },

        resolveAvailability: function() {
            var baseResult = null;

            if (0 < this.bundleSlots.length) {
                $.each(this.bundleSlots, function () {
                    if (this.hasChoice()) {
                        var result = this.getChoice().resolveAvailability();
                        if (!baseResult) {
                            baseResult = result;
                        } else {
                            baseResult.merge(result);
                        }
                    }
                });
            } else if (this.variant && this.variant.hasVariant()) {
                baseResult = Availability.resolve.call(this.variant.getVariant().availability, this.getTotalQuantity());
            } else {
                baseResult = Availability.resolve.call(this.config.availability, this.getTotalQuantity());
            }

            baseResult.merge(this.optionGroups.resolveAvailability());

            return baseResult;
        },

        updatePricesAndAvailability: function() {
            var that = this, display = '', availability = this.resolveAvailability();
            this.originalPrice = 0;
            this.finalPrice = 0;

            this.$availability.empty();
            this.$quantity.closest('.form-group').removeClass('has-error has-warning');

            if (this.$submitButton) {
                this.$submitButton.prop('disabled', false);
            }

            if (0 < this.bundleSlots.length) {
                $.each(this.bundleSlots, function () {
                    if (this.hasChoice()) {
                        that.originalPrice += this.getChoice().getOriginalPrice();
                        that.finalPrice  += this.getChoice().getFinalPrice();
                    }
                });
            } else {
                var pricing = this.config.pricing;
                if (this.variant && this.variant.hasVariant()) {
                    pricing = this.variant.getVariant().pricing;
                }

                this.originalPrice = Pricing.calculate.call(pricing, this.totalQuantity, false) * this.totalQuantity;
                this.finalPrice = Pricing.calculate.call(pricing, this.totalQuantity) * this.totalQuantity;
            }

            this.originalPrice += this.optionGroups.getOriginalPrice() * this.totalQuantity;
            this.finalPrice += this.optionGroups.getFinalPrice() * this.totalQuantity;

            if (this.originalPrice !== this.finalPrice) {
                display = '<del>' + this.originalPrice.formatPrice(Pricing.currency) + '</del>&nbsp;';
            }

            display += this.finalPrice.formatPrice(Pricing.currency);

            this.$pricing.html(Templates['sale_item_pricing.html.twig'].render({
                label: SaleItem.trans.total + '&nbsp;' + SaleItem.trans[Pricing.mode],
                price: display
            }));

            if (availability.hasClass()) {
                this.$quantity.closest('.form-group').addClass('has-' + availability.getClass());

                if (availability.level === 3 && !this.parentItem && this.$submitButton && !this.config.privileged) {
                    this.$submitButton.prop('disabled', true);
                }
            }

            this.$availability.html(availability.display());
        },

        getConfig: function () {
            return this.config;
        },

        getQuantity: function () {
            return this.quantity;
        },

        getTotalQuantity: function () {
            return this.totalQuantity;
        },

        getOriginalPrice: function () {
            return this.originalPrice;
        },

        getFinalPrice: function () {
            return this.finalPrice;
        }
    });

    $.fn.saleItem = function (parentItem) {
        return this.each(function () {
            if (undefined === $(this).data('saleItem')) {
                $(this).data('saleItem', new SaleItem($(this), parentItem));
            }
        });
    };

    $(document).on('click', '.sale-item-configure .item-gallery a', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var src = String($(this).attr('href'));
        if (src.length) {
            require(['fancybox'], function() {
                $.fancybox.open({
                    src: src,
                    caption: $(this).attr('title'),
                    protect: true
                });
            });
        }

        return false;
    });

    return {
        init: function ($element) {
            $element.saleItem();
        },
        destroy: function ($element) {
            var saleItem = $element.data('saleItem');
            if (undefined !== saleItem) {
                saleItem.destroy();
            }
        }
    };
});

define(['jquery', 'ekyna-product/templates', 'ekyna-number', 'fancybox'], function ($, Templates) {
    "use strict";


    var toggleDisabled = function ($element, disabled) {
        $element
            .prop('disabled', disabled)
            .find('input, select, button, textarea')
            .not('[data-locked="1"]')
            .prop('disabled', disabled);
    };

    var roundPrice = function (price) {
        return Math.round(parseFloat(price) * 100) / 100;
    };

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
        },

        getLabel: function () {
            return this.$choice.find('.product-title').html();
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

    function OptionGroup($element, optionGroups) {
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

            this.$availability = this.$element.find('.sale-item-option-availability');

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
            this.$option = this.option = undefined;

            var $option = this.$select.find('option[value="' + this.$select.val() + '"]');

            if (1 === $option.length && $option.data('config')) {
                this.$option = $option;
                this.option = $option.data('config');

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

            this.updateAvailability();
        },

        updateAvailability: function () {
            this.$availability.empty();
            this.$element.removeClass('has-error');

            if (!(this.option && this.option.availability)) {
                return;
            }

            var quantity = this.optionGroups.item.getTotalQuantity(),
                config = {
                    min: '0',
                    max: '0',
                    message: null
                };

            $.extend(config, this.option.availability);

            var min = parseFloat(config.min),
                max = config.max === 'INF' ? Number.POSITIVE_INFINITY : parseFloat(config.max),
                message = null;

            if (0 === max) {
                message = config.message;
            } else if (0 < max && quantity > max) {
                message = SaleItem.trans.max_quantity.replace('%max%', max.toLocaleString());
            } else if (0 < min && quantity < min) {
                message = SaleItem.trans.min_quantity.replace('%min%', min.toLocaleString());
            } else {
                return;
            }

            this.$element.addClass('has-error');
            this.$availability.html(message);
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

        getPrice: function () {
            return this.hasOption() ? roundPrice(this.option.price) : 0;
        },

        getLabel: function () {
            return this.hasOption() ? this.$option.text() : '';
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

    function OptionGroups($element, item) {
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
                that.groups.push($group.data('optionGroup'))
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

        updateAvailability: function() {
            $.each(this.groups, function () {
                this.updateAvailability();
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

    function Variant($element, item) {
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
            this.config = {
                label: null,
                thumb: null,
                price: 0,
                groups: [],
                availability: null
            };

            var $variant = this.$element.find('option[value="' + this.$element.val() + '"]');
            if (1 === $variant.size() && $variant.data('config')) {
                this.$variant = $variant;
                this.config = $.extend(this.config, $variant.data('config'), {
                    label: $variant.text()
                });
                if (this.$image && this.config.thumb) {
                    this.$image
                        .attr('href', this.config.image)
                        .attr('title', this.config.label)
                        .find('img')
                        .attr('src', this.config.thumb);

                    return;
                }
            }

            if (this.$image) {
                this.$image
                    .attr('href', this.$image.data('href'))
                    .attr('title', this.$image.data('title'));

                var $img = this.$image.find('img');
                $img.attr('src', $img.data('src'));
            }
        },

        hasVariant: function () {
            return undefined !== this.$variant;
        },

        getConfig: function () {
            return this.config;
        },

        getPrice: function () {
            return roundPrice(this.config.price);
        },

        getLabel: function () {
            return this.config.label;
        },

        getOptionGroups: function () {
            return this.config.groups;
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

    function SaleItem($element, parentItem) {
        this.$element = $element;
        this.parentItem = parentItem;

        this.init();
    }

    SaleItem.trans = {
        quantity: 'Quantity',
        discount: 'Discount',
        unit_price: 'Unit price',
        total: 'Net total',
        rule_table: 'Your prices',
        price_table: 'Detailed unit price',
        min_quantity: 'Minimum quantity is %min%',
        max_quantity: 'Maximum quantity is %max%'
    };

    $.extend(SaleItem.prototype, {
        init: function () {
            this.id = this.$element.attr('id');

            this.config = $.extend({
                price: 0,
                currency: 'EUR',
                rules: [],
                availability: null,
                privileged: false
            }, this.$element.data('config'));

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
            this.quantity = this.$quantity.val();
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
            this.activeRule = undefined;
            this.basePrice = 0;
            this.unitPrice = 0;
            this.totalPrice = 0;

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
                    that.onVariantChange();
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

        onVariantChange: function () {
            this.createVariantOptionGroups();
            this.onChildChange();
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

        onChildChange: function () {
            this.calculatePrices();
            this.updatePricing();
            this.updateAvailability();
        },

        updateParentQuantity: function () {
            if (this.parentItem) {
                if (1 < this.parentItem.getQuantity()) {
                    this.$parentQuantity.html(this.parentItem.getQuantity() + 'x').show();
                } else {
                    this.$parentQuantity.hide();
                }
            }
        },

        onQuantityChange: function () {
            this.quantity = this.$quantity.val();
            this.totalQuantity = this.quantity;

            if (this.parentItem) {
                this.totalQuantity = this.quantity * this.parentItem.getTotalQuantity();
            }

            $.each(this.bundleSlots, function () {
                this.updateQuantity();
            });

            this.updateParentQuantity();
            this.onChange();
        },

        onChange: function () {
            this.resolveActiveRule();
            this.calculatePrices();
            this.updatePricing();
            this.updateAvailability();
        },

        updateAvailability: function () {
            /*if (0 === this.$availabilityMessage.size()) {
                return;
            }*/

            this.optionGroups.updateAvailability();

            this.$availability.empty();
            this.$quantity.closest('.form-group').removeClass('has-error');

            if (!this.parentItem && this.$submitButton && !this.config.privileged) {
                this.$quantity.prop('disabled', false);
                this.$submitButton.prop('disabled', false);
            }

            var config = this.config;
            if (this.variant && this.variant.hasVariant()) {
                config = this.variant.getConfig();
            }

            if (null === config.availability) {
                return;
            }

            config = $.extend({
                min: 0,
                max: 0,
                message: null
            }, config.availability);

            var min = parseFloat(config.min),
                max = config.max === 'INF' ? Number.POSITIVE_INFINITY : parseFloat(config.max),
                disableQuantity = false,
                disableSubmit = true,
                message = null;

            if (0 === max) {
                message = config.message;
                disableQuantity = true;
            } else if (0 < max && this.totalQuantity > max) {
                message = SaleItem.trans.max_quantity.replace('%max%', max.toLocaleString());
            } else if (0 < min && this.totalQuantity < min) {
                message = SaleItem.trans.min_quantity.replace('%min%', min.toLocaleString());
            } else {
                disableSubmit = false;
                if (this.parentItem) return;
            }

            if (disableQuantity || disableSubmit) {
                if (!this.parentItem && this.$submitButton && !this.config.privileged) {
                    this.$quantity.prop('disabled', disableQuantity);
                    this.$submitButton.prop('disabled', disableSubmit);
                }

                this.$quantity.closest('.form-group').addClass('has-error');
            }

            this.$availability.html(message);
        },

        resolveActiveRule: function () {
            if (0 < this.config.rules.length) {
                var activeRule = undefined,
                    quantity = this.totalQuantity;

                $.each(this.config.rules, function (i, rule) {
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

        calculatePrices: function () {
            var basePrice = 0, unitPrice, totalPrice;

            if (0 < this.bundleSlots.length) {
                $.each(this.bundleSlots, function () {
                    if (this.hasChoice()) {
                        basePrice += this.getChoice().getUnitPrice() * this.getChoice().getQuantity();
                    }
                });
            } else {
                if (this.variant) {
                    basePrice = this.variant.getPrice();
                } else {
                    basePrice = roundPrice(this.config.price);
                }
            }

            basePrice += this.optionGroups.getPrice();


            unitPrice = basePrice;
            if (this.activeRule) {
                unitPrice *= 1 - parseFloat(this.activeRule.percent) / 100;
            }

            totalPrice = unitPrice * this.totalQuantity;

            var changed = (basePrice !== this.basePrice || unitPrice !== this.unitPrice || totalPrice !== this.totalPrice);

            this.basePrice = basePrice;
            this.unitPrice = unitPrice;
            this.totalPrice = totalPrice;

            return changed;
        },

        updatePricing: function () {
            if (0 === this.$pricing.size()) {
                return;
            }

            var that = this, quantity = this.totalQuantity, lines = [], rules = [];

            var data = {
                detailed: false,
                trans: SaleItem.trans,
                quantity: quantity,
                base: this.basePrice,
                unit: this.unitPrice,
                basePrice: this.basePrice.formatPrice(that.config.currency),
                unitPrice: this.unitPrice.formatPrice(that.config.currency),
                totalPrice: this.totalPrice.formatPrice(that.config.currency)
            };

            // Rules
            if (0 < this.config.rules.length) {
                data.detailed = true;
                $(this.config.rules).each(function (i, rule) {
                    var percent = parseFloat(rule.percent),
                        price = that.basePrice * (1 - percent / 100);
                    rules.push({
                        label: rule.label,
                        f_percent: rule.percent.toLocaleString() + '%',
                        f_price: price.formatPrice(that.config.currency),
                        active: that.activeRule && that.activeRule.id === rule.id
                    });
                });
            }
            data.rules = rules.reverse();

            // Lines
            $.each(this.bundleSlots, function () {
                if (this.hasChoice()) {
                    var price = this.getChoice().getUnitPrice() * this.getChoice().getQuantity();
                    lines.push({
                        label: this.getLabel(),
                        price: price.formatPrice(that.config.currency)
                    });
                }
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
                        label: SaleItem.trans.discount + ' ' + percent.toLocaleString() + '%',
                        price: '-' + price.formatPrice(that.config.currency)
                    });
                }
                lines.push({
                    label: SaleItem.trans.unit_price,
                    price: this.unitPrice.formatPrice(that.config.currency),
                    class: 'info'
                });
            }
            data.lines = lines;

            this.$pricing.html(Templates['sale_item_pricing.html.twig'].render(data));
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

        getUnitPrice: function () {
            return this.unitPrice;
        },

        getTotalPrice: function () {
            return this.totalPrice;
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
            $.fancybox.open({
                src: src,
                caption: $(this).attr('title'),
                protect: true
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


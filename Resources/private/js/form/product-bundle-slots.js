define(['jquery'], function ($) {
    "use strict";

    /* -------------------------- BundleRules -------------------------- */

    function BundleRules($element) {
        this.$element = $element;
        this.$element.data('bundleRules', this);

        this.locked = false;
        this.$group = this.$element.closest('.form-group');

        this.init();
    }

    BundleRules.prototype.init = function () {

    };

    BundleRules.prototype.save = function () {
        if (this.locked) {
            // TODO Remove conditions / rules
        }
    };

    BundleRules.prototype.destroy = function () {

    };

    BundleRules.prototype.lock = function () {
        if (this.locked) {
            return;
        }

        this.locked = true;

        this.$group
            .hide()
            .find('select,input').prop('disabled', true);
    };

    BundleRules.prototype.unlock = function () {
        if (!this.locked) {
            return;
        }

        this.locked = false;

        this.$group
            .show()
            .find('select,input').prop('disabled', false);
    };

    $.fn.bundleRules = function () {
        return this.each(function () {
            if (undefined === $(this).data('bundleRules')) {
                new BundleRules($(this));
            }
        });
    };

    /* -------------------------- BundleChoice -------------------------- */

    function BundleChoice($element, configurable, slotIndex) {
        this.$element = $element;
        this.$element.data('bundleChoice', this);

        this.configurable = configurable;
        this.slotIndex = slotIndex;

        this.$product = $element.find('#' + $element.attr('id') + '_product');
        this.$options = $element.find('#' + $element.attr('id') + '_excludedOptionGroups');

        this.init();
    }

    BundleChoice.prototype.init = function () {
        if (this.configurable) {
            this.$number = $('<span class="bundle-choice-index"></span>').appendTo(this.$element);
            this.update();
        }

        this.$product.on('change', $.proxy(this.onProductChange, this));
    };

    BundleChoice.prototype.save = function () {

    };

    BundleChoice.prototype.destroy = function () {
        this.$product.off('change');
    };

    BundleChoice.prototype.setSlotIndex = function (index) {
        this.slotIndex = index;

        if (this.configurable) {
            this.update();
        }
    };

    BundleChoice.prototype.update = function () {
        if (!this.configurable) {
            return;
        }
        this.$number.text(this.slotIndex + '.' + this.$element.index());
    };

    BundleChoice.prototype.onProductChange = function () {
        this.$options.empty();

        var $options = this.$options,
            id = this.$options.attr('id'),
            name = this.$options.data('name');

        var selection = this.$product.select2('data');
        if (selection.length) {
            var product = selection[0];

            if (!product.hasOwnProperty('option_groups')) {
                if (!product.hasOwnProperty('element')) {
                    return;
                }

                product = $(product.element).data('entity');
            }

            if (!product.hasOwnProperty('option_groups')) {
                return;
            }

            $.each(product.option_groups, function (i, group) {
                $('<label for="' + id + '_' + i + '" class="checkbox-inline">' +
                    '<input type="checkbox" id="' + id + '_' + i + '" name="' + name + '[]" value="' + group.id + '" checked="checked">' +
                    '[' + (group.required ? 'Required' : 'Optional') + '] ' + group.name +
                '</label>').appendTo($options);
            });
        }
    };

    $.fn.bundleChoice = function (configurable, slotIndex) {
        return this.each(function () {
            if (undefined === $(this).data('bundleChoice')) {
                new BundleChoice($(this), configurable, slotIndex);
            }
        });
    };

    /* -------------------------- BundleChoices -------------------------- */

    function BundleChoices($element, configurable, slotIndex) {
        this.$element = $element;
        this.$element.data('bundleChoices', this);
        this.configurable = configurable;
        this.slotIndex = slotIndex;

        this.init();
    }

    BundleChoices.prototype.init = function () {
        this.$choices = this.$element.find('> ul > li').bundleChoice(this.configurable, this.slotIndex);

        if (this.configurable) {
            var onChange = $.proxy(this.onChange, this);
            this.$element.on('ekyna-collection-field-added', onChange);
            this.$element.on('ekyna-collection-field-removed', onChange);
            this.$element.on('ekyna-collection-field-moved-up', onChange);
            this.$element.on('ekyna-collection-field-moved-down', onChange);
        }
    };

    BundleChoices.prototype.save = function () {

    };

    BundleChoices.prototype.destroy = function () {

    };

    BundleChoices.prototype.setSlotIndex = function (index) {
        this.slotIndex = index;
        if (this.configurable) {
            this.$choices.each(function () {
                $(this).data('bundleChoice').setSlotIndex(index);
            });
        }
    };

    BundleChoices.prototype.onChange = function () {
        if (!this.configurable) {
            return;
        }

        var slotIndex = this.slotIndex;

        this.$choices = this.$element.find('> ul > li')
            .bundleChoice(this.configurable, this.slotIndex)
            .each(function () {
                $(this).data('bundleChoice').setSlotIndex(slotIndex);
            });
    };

    $.fn.bundleChoices = function (configurable, slotIndex) {
        return this.each(function () {
            if (undefined === $(this).data('bundleChoices')) {
                new BundleChoices($(this), configurable, slotIndex);
            }
        });
    };

    /* -------------------------- BundleSlot -------------------------- */

    function BundleSlot($element, configurable) {
        this.$element = $element;
        this.$element.data('bundleSlot', this);
        this.configurable = configurable;
        this.init();
    }

    BundleSlot.prototype.init = function () {
        this.id = this.$element.attr('id');

        var index = this.$element.index();
        this.$choices = this.$element.find('#' + this.id + '_choices').bundleChoices(this.configurable, index);

        if (this.configurable) {
            this.$number = $('<span class="bundle-slot-index"></span>')
                .text(index)
                .appendTo(this.$element);

            this.$required = this.$element.find('#' + this.id + '_required');
            this.$rules = this.$element.find('#' + this.id + '_rules').bundleRules();

            this.$required.on('change', $.proxy(this.onRequiredChange, this));
            this.onRequiredChange();
        } else {
            // TODO
        }

    };

    BundleSlot.prototype.save = function () {
        if (this.configurable) {
            this.$rules.data('bundleRules').save();
        }
    };

    BundleSlot.prototype.update = function () {
        if (!this.configurable) {
            return;
        }
        var index = this.$element.index();
        this.$number.text(index);
        this.$choices.data('bundleChoices').setSlotIndex(index);
    };

    BundleSlot.prototype.destroy = function () {
        if (this.configurable) {
            this.$required.off('change');
        }
    };

    BundleSlot.prototype.onRequiredChange = function () {
        if (!this.configurable) {
            return;
        }
        if (this.$required.is(':checked')) {
            this.$rules.data('bundleRules').lock();
        } else {
            this.$rules.data('bundleRules').unlock();
        }
    };

    $.fn.bundleSlot = function (configurable) {
        return this.each(function () {
            if (undefined === $(this).data('bundleSlot')) {
                new BundleSlot($(this), configurable);
            }
        });
    };

    /* -------------------------- BundleSlots -------------------------- */

    function BundleSlots($element) {
        this.$element = $element;
        this.$element.data('bundleSlots', this);

        this.configurable = $element.hasClass('configurable');
        this.init();
    }

    BundleSlots.prototype.init = function () {
        this.$slots = this.$element.find('> ul > li').bundleSlot(this.configurable);

        var onChange = $.proxy(this.onChange, this);
        this.$element.on('ekyna-collection-field-added', onChange);
        this.$element.on('ekyna-collection-field-removed', onChange);
        this.$element.on('ekyna-collection-field-moved-up', onChange);
        this.$element.on('ekyna-collection-field-moved-down', onChange);
    };

    BundleSlots.prototype.save = function () {
        this.$slots.each(function () {
            $(this).data('bundleSlot').save();
        });
    };

    BundleSlots.prototype.destroy = function () {
        this.$element.off('ekyna-collection-field-added');
        this.$element.off('ekyna-collection-field-removed');
        this.$element.off('ekyna-collection-field-moved-up');
        this.$element.off('ekyna-collection-field-moved-down');

        this.$slots.each(function () {
            $(this).data('bundleSlot').destroy();
        });
    };

    BundleSlots.prototype.onChange = function () {
        this.$slots = this.$element
            .find('> ul > li')
            .bundleSlot(this.configurable)
            .each(function () {
                $(this).data('bundleSlot').update();
            });
    };

    $.fn.bundleSlots = function () {
        return this.each(function () {
            if (undefined === $(this).data('bundleSlots')) {
                new BundleSlots($(this));
            }
        });
    };

    /* -------------------------- Export -------------------------- */

    return {
        init: function ($element) {
            $element.bundleSlots();
        },
        save: function ($element) {
            var bundleSlots = $element.data('bundleSlots');
            if (undefined !== bundleSlots) {
                bundleSlots.destroy();
            }
        },
        destroy: function ($element) {
            var bundleSlots = $element.data('bundleSlots');
            if (undefined !== bundleSlots) {
                bundleSlots.destroy();
            }
        }
    };
});

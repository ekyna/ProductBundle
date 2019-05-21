define(['jquery', 'jquery-ui/widget'], function ($) {
    "use strict";

    /* -------------------------- BundleRules -------------------------- */

    function BundleRules($element) {
        this.$element = $element;
        this.$element.data('bundleRules', this);

        this.locked = false;
        this.$group = this.$element.closest('.form-group');

        this.init();
    }

    BundleRules.prototype.init = function() {

    };

    BundleRules.prototype.save = function() {
        if (this.locked) {
            // TODO Remove conditions / rules
        }
    };

    BundleRules.prototype.destroy = function() {

    };

    BundleRules.prototype.lock = function() {
        if (this.locked) {
            return;
        }

        this.locked = true;

        this.$group
            .hide()
            .find('select,input').prop('disabled', true);
    };

    BundleRules.prototype.unlock = function() {
        if (!this.locked) {
            return;
        }

        this.locked = false;

        this.$group
            .show()
            .find('select,input').prop('disabled', false);
    };

    $.fn.bundleRules = function() {
        return this.each(function () {
            if (undefined === $(this).data('bundleRules')) {
                new BundleRules($(this));
            }
        });
    };

    /* -------------------------- BundleChoice -------------------------- */

    function BundleChoice($element, slotIndex) {
        this.$element = $element;
        this.slotIndex = slotIndex;
        this.$element.data('bundleChoice', this);

        this.init();
    }

    BundleChoice.prototype.init = function() {
        this.$number = $('<span class="bundle-choice-index"></span>').appendTo(this.$element);
        this.update();
    };

    BundleChoice.prototype.save = function() {

    };

    BundleChoice.prototype.destroy = function() {

    };

    BundleChoice.prototype.setSlotIndex = function(index) {
        this.slotIndex = index;
        this.update();
    };

    BundleChoice.prototype.update = function() {
        this.$number.text(this.slotIndex + '.' + this.$element.index());
    };

    $.fn.bundleChoice = function(slotIndex) {
        return this.each(function () {
            if (undefined === $(this).data('bundleChoice')) {
                new BundleChoice($(this), slotIndex);
            }
        });
    };

    /* -------------------------- BundleChoices -------------------------- */

    function BundleChoices($element, slotIndex) {
        this.$element = $element;
        this.$element.data('bundleChoices', this);

        this.slotIndex = slotIndex;

        this.init();
    }

    BundleChoices.prototype.init = function() {
        this.$choices = this.$element.find('> ul > li').bundleChoice(this.slotIndex);

        var onChange = $.proxy(this.onChange, this);
        this.$element.on('ekyna-collection-field-added', onChange);
        this.$element.on('ekyna-collection-field-removed', onChange);
        this.$element.on('ekyna-collection-field-moved-up', onChange);
        this.$element.on('ekyna-collection-field-moved-down', onChange);
    };

    BundleChoices.prototype.save = function() {

    };

    BundleChoices.prototype.destroy = function() {

    };

    BundleChoices.prototype.setSlotIndex = function(index) {
        this.slotIndex = index;
        this.$choices.each(function() {
            $(this).data('bundleChoice').setSlotIndex(index);
        });
    };

    BundleChoices.prototype.onChange = function() {
        var slotIndex = this.slotIndex;

        this.$choices = this.$element.find('> ul > li')
            .bundleChoice(this.slotIndex)
            .each(function() {
                $(this).data('bundleChoice').setSlotIndex(slotIndex);
            });
    };

    $.fn.bundleChoices = function(slotIndex) {
        return this.each(function () {
            if (undefined === $(this).data('bundleChoices')) {
                new BundleChoices($(this), slotIndex);
            }
        });
    };

    /* -------------------------- BundleSlot -------------------------- */

    function BundleSlot($element) {
        this.$element = $element;
        this.$element.data('bundleSlot', this);

        this.init();
    }

    BundleSlot.prototype.init = function() {
        this.id = this.$element.attr('id');

        var index = this.$element.index();
        this.$number = $('<span class="bundle-slot-index"></span>')
            .text(index)
            .appendTo(this.$element);

        this.$required = this.$element.find('#' + this.id + '_required');
        this.$rules = this.$element.find('#' + this.id + '_rules').bundleRules();
        this.$choices = this.$element.find('#' + this.id + '_choices').bundleChoices(index);

        this.$required.on('change', $.proxy(this.onRequiredChange, this));
        this.onRequiredChange();
    };

    BundleSlot.prototype.save = function() {
        this.$rules.data('bundleRules').save();
    };

    BundleSlot.prototype.update = function() {
        var index = this.$element.index();
        this.$number.text(index);
        this.$choices.data('bundleChoices').setSlotIndex(index);
    };

    BundleSlot.prototype.destroy = function() {
        this.$required.off('change');
    };

    BundleSlot.prototype.onRequiredChange = function() {
        if (this.$required.is(':checked')) {
            this.$rules.data('bundleRules').lock();
        } else {
            this.$rules.data('bundleRules').unlock();
        }
    };

    $.fn.bundleSlot = function() {
        return this.each(function () {
            if (undefined === $(this).data('bundleSlot')) {
                new BundleSlot($(this));
            }
        });
    };

    /* -------------------------- BundleSlots -------------------------- */

    function BundleSlots($element) {
        this.$element = $element;
        this.$element.data('bundleSlots', this);

        this.init();
    }

    BundleSlots.prototype.init = function() {
        this.$slots = this.$element.find('> ul > li').bundleSlot();

        var onChange = $.proxy(this.onChange, this);
        this.$element.on('ekyna-collection-field-added', onChange);
        this.$element.on('ekyna-collection-field-removed', onChange);
        this.$element.on('ekyna-collection-field-moved-up', onChange);
        this.$element.on('ekyna-collection-field-moved-down', onChange);
    };

    BundleSlots.prototype.save = function() {
        this.$slots.each(function() {
            $(this).data('bundleSlot').save();
        });
    };

    BundleSlots.prototype.destroy = function() {
        this.$element.off('ekyna-collection-field-added');
        this.$element.off('ekyna-collection-field-removed');
        this.$element.off('ekyna-collection-field-moved-up');
        this.$element.off('ekyna-collection-field-moved-down');

        this.$slots.each(function() {
            $(this).data('bundleSlot').destroy();
        });
    };

    BundleSlots.prototype.onChange = function() {
        this.$slots = this.$element
            .find('> ul > li')
            .bundleSlot()
            .each(function() {
                $(this).data('bundleSlot').update();
            });
    };

    $.fn.bundleSlots = function() {
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

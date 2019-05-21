define(['jquery', 'jquery-ui/widget'], function ($) {
    "use strict";

    $.widget('ekyna_product.bundleRules', {
        _create: function () {
            this.bindEvents();
        },
        _destroy: function () {
            this.unbindEvents();
        },
        save: function () {

        },
        lock: function() {
            this.disable();
            this._hide(this.element);
        },
        unlock: function() {
            this.enable();
            this._show(this.element);
        },
        bindEvents: function () {

        },
        unbindEvents: function () {

        }
    });

    $.widget('ekyna_product.bundleChoice', {
        _create: function () {
            this.bindEvents();
        },
        _destroy: function () {
            this.unbindEvents();
        },
        save: function () {

        },
        bindEvents: function () {

        },
        unbindEvents: function () {

        }
    });

    $.widget('ekyna_product.bundleChoices', {
        _create: function () {
            this.bindEvents();
        },
        _destroy: function () {
            this.unbindEvents();
        },
        save: function () {

        },
        bindEvents: function () {

        },
        unbindEvents: function () {

        }
    });

    $.widget('ekyna_product.bundleSlot', {
        _create: function () {
            this.id = this.element.attr('id');

            this.$required = this.element.find('#' + this.id + '_required');
            this.$rules = this.element.find('#' + this.id + '_rules');
            this.$rules.bundleRules();

            this.bindEvents();
        },
        _destroy: function () {
            this.unbindEvents();
        },
        update: function() {

        },
        save: function () {

        },
        bindEvents: function () {
            this._on(this.$required, {'change': this.onRequiredChange});
        },
        unbindEvents: function () {
            this._off(this.$required, 'change');
        },
        onRequiredChange: function() {
            if (1 === this.$required.val()) {
                this.$rules.bundleRules('lock');
            } else {
                this.$rules.bundleRules('unlock');
            }
        }
    });

    $.widget('ekyna_product.bundleSlots', {
        _create: function () {
            console.log('Bundle slots');

            this.bindEvents();

            this.element.find('> ul > li').bundleSlot();
        },
        _destroy: function () {
            this.bindEvents();

            this.element.find('> ul > li').bundleSlot('destroy');
        },
        save: function () {

        },
        bindEvents: function () {
            this._on(this.element, {
                'ekyna-collection-field-added': this.onSlotAdded,
                'ekyna-collection-field-removed': this.onSlotRemoved,
                'ekyna-collection-field-moved-up': this.onSlotMovedUp,
                'ekyna-collection-field-moved-down': this.onSlotMovedDown
            });
        },
        unbindEvents: function () {
            this._off(
                this.element,
                'ekyna-collection-field-added ekyna-collection-field-removed ' +
                'ekyna-collection-field-moved-up ekyna-collection-field-moved-down'
            );
        },
        onSlotAdded: function () {
            console.log('onSlotAdded');
        },
        onSlotRemoved: function () {
            console.log('onSlotRemoved');
        },
        onSlotMovedUp: function () {
            console.log('onSlotMovedUp');
        },
        onSlotMovedDown: function () {
            console.log('onSlotMovedDown');
        }
    });

    return {
        init: function ($element) {
            $element.bundleSlots();
        },
        save: function ($element) {
            if ($element.data('ekyna_product.bundleSlots')) {
                $element.bundleSlots('save');
            }
        },
        destroy: function ($element) {
            if ($element.data('ekyna_product.bundleSlots')) {
                $element.bundleSlots('destroy');
            }
        }
    };
});
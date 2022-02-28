define(['jquery', 'jquery-ui/ui/widget'], function ($) {
    "use strict";

    $.widget('ekyna_product.batchEdit', {
        _create: function () {
            this.$togglers = this.element.find('input[type=checkbox][data-toggle-field]');

            this._on(this.$togglers, {'change': this._onTogglerChange});

            this._onTogglerChange();
        },
        _destroy: function () {
            this._off(this.$togglers, 'change');

            this.$togglers = undefined;
        },
        _onTogglerChange: function () {
            var form = this.element,
                name = form.attr('name');
            this.$togglers.each(function() {
                var n = '[name="' + name + '[' + $(this).data('toggle-field')  + ']"]',
                    f = form.find(n),
                    v = !$(this).is(':checked');

                f.prop('disabled', v)
            });
        },
        save: function () {
            /*var mode = this.$mode.filter(':checked').val();
            if (mode === 'data') {
                this.$product.val(undefined).find('option:selected').prop('selected', false);
                this.$cascade.prop('checked', false);
            } else if (mode === 'product') {
                this.$dataWrapper.find('input, select').val(undefined);
            }*/
        }
    });

    return {
        init: function ($element) {
            $element.batchEdit();
        },
        save: function ($element) {
            if ($element.data('ekyna_product.batchEdit')) {
                $element.batchEdit('save');
            }
        },
        destroy: function ($element) {
            if ($element.data('ekyna_product.batchEdit')) {
                $element.batchEdit('destroy');
            }
        }
    };
});

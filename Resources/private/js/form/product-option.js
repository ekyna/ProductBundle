define(['jquery', 'jquery-ui/widget', 'select2', 'ekyna-commerce/form/price'], function ($) {
    "use strict";

    $.widget('ekyna_product.optionType', {
        _create: function () {
            this.$mode = this.element.find('div.option-mode input[type="radio"]');
            this.$price = this.element.find('div.commerce-price');
            this.$productWrapper = this.element.find('> .option-product');
            this.$dataWrapper = this.element.find('> .option-data');

            this.$product = this.$productWrapper.find('select.resource-search');
            this.$cascade = this.$productWrapper.find('input.product-cascade');
            this.$taxGroup = this.$dataWrapper.find('.tax-group-choice');

            if (
                2 !== this.$mode.length ||
                1 !== this.$price.length ||
                1 !== this.$product.length ||
                1 !== this.$cascade.length ||
                1 !== this.$taxGroup.length
            ) {
                throw 'Missing product option type fields';
            }

            this._on(this.$mode, {'change': this._onModeChange});

            this._onModeChange();
        },
        _destroy: function () {
            this._off(this.$mode, 'change');
            this._off(this.$product, 'change');
            this._off(this.$taxGroup, 'change');

            this.$mode = undefined;
            this.$product = undefined;
            this.$taxGroup = undefined;
        },
        _onModeChange: function () {
            this.$dataWrapper.hide();
            this.$productWrapper.hide();

            this._off(this.$taxGroup, 'change');
            this._off(this.$product, 'change');

            var mode = this.$mode.filter(':checked').val();
            if (mode === 'product') {
                this._on(this.$product, {'change': this._onProductChange});
                this._onProductChange();
                this.$productWrapper.show();
            } else if (mode === 'data') {
                this._on(this.$taxGroup, {'change': this._onTaxGroupChange});
                this._onTaxGroupChange();
                this.$dataWrapper.show();
            }
        },
        _onProductChange: function () {
            var id = null,
                data = this.$product.select2('data');
            if (data && data[0]) {
                if (data[0].tax_group) {
                    id = data[0].tax_group;
                } else {
                    var entity = $(data[0].element).data('entity');
                    if (entity && entity.tax_group) {
                        id = entity.tax_group;
                    }
                }
            }

            this.$price.priceType('option', 'taxes', this._getTaxes(id));
        },
        _onTaxGroupChange: function () {
            this.$price.priceType('option', 'taxes', this._getTaxes(this.$taxGroup.val()));
        },
        _getTaxes: function (id) {
            if (!id) {
                return [];
            }

            var taxes = [],
                data = this.$taxGroup.find('option[value="' + id + '"]').data('taxes');

            if (data) {
                $.each(data, function (index, value) {
                    taxes.push(value.rate);
                });
            }

            return taxes;
        },
        save: function () {
            var mode = this.$mode.filter(':checked').val();
            if (mode === 'data') {
                this.$product.val(undefined).find('option:selected').prop('selected', false);
                this.$cascade.prop('checked', false);
            } else if (mode === 'product') {
                this.$dataWrapper.find('input, select').val(undefined);
            }
        }
    });

    return {
        init: function ($element) {
            $element.optionType();
        },
        save: function ($element) {
            if ($element.data('ekyna_product.optionType')) {
                $element.optionType('save');
            }
        },
        destroy: function ($element) {
            if ($element.data('ekyna_product.optionType')) {
                $element.optionType('destroy');
            }
        }
    };
});

define(['jquery'], function($) {
    "use strict";

    /**
     * Option widget
     */
    $.fn.optionWidget = function() {

        this.each(function() {

            var $form = $(this),
                $mode = $form.find('div.option-mode input[type="radio"]'),
                $product = $form.find('select.entity-search');

            function onChange() {
                var mode = $mode.filter(':checked').val();

                $form.find('.option-wrapper').hide();
                $form.find('.option-' + mode).show();

                if (mode === 'data') {
                    $product.val(undefined).find('option:selected').prop('checked', false);
                }
            }

            $mode.on('change', onChange);

            onChange();
        });

        return this;
    };

    return {
        init: function($element) {
            $element.optionWidget();
        }
    };
});
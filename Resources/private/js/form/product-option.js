define(['jquery'], function($) {
    "use strict";

    /**
     * Option widget
     */
    $.fn.optionWidget = function() {

        this.each(function() {

            var $form = $(this),
                $mode = $form.find('div.option-mode input[type="radio"]');

            function onChange() {
                var mode = $mode.filter(':checked').val();

                $form.find('.option-wrapper').hide();
                $form.find('.option-' + mode).show();
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

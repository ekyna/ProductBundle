define(['jquery', 'routing', 'ekyna-form', 'ekyna-ui'], function($, Router, Form) {
    "use strict";

    /**
     * Attributes widget
     */
    $.fn.attributesWidget = function() {

        this.each(function() {
            var $attributes = $(this),
                $attributeSet = $attributes.closest('form').find($attributes.data('set-field')),
                xhr = null;

            if (1 !== $attributeSet.length) {
                return;
            }

            function loadAttributesForm() {
                if (xhr) {
                    xhr.abort();
                    xhr = null;
                }

                var setId = parseInt($attributeSet.val()) || 0;
                if (0 >= setId) {
                    $attributes.empty();
                    return;
                }

                $attributes.loadingSpinner();

                xhr = $.ajax({
                    url: Router.generate('ekyna_product_product_admin_attributes_form', {
                        'attributeSetId': setId
                    }),
                    dataType: 'xml'
                });

                xhr.done(function(xml) {
                    $attributes.loadingSpinner('off');

                    $attributes.empty();

                    var $form = $(xml).find('form');
                    if (1 !== $form.length) {
                        return;
                    }

                    $attributes.append($($form.text()).children());

                    var form = Form.create($attributes);
                    form.init();
                });
            }

            $attributeSet.on('change', loadAttributesForm);
        });

        return this;
    };

    return {
        init: function($element) {
            $element.attributesWidget();
        }
    };
});

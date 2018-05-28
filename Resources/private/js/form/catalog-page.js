define(['jquery', 'routing', 'ekyna-form', 'ekyna-ui'], function($, Router, Form) {
    "use strict";

    /**
     * Catalog page widget
     */
    $.fn.catalogPageWidget = function() {

        this.each(function() {

            var $page = $(this),
                $template = $page.find('select.catalog-page-template'),
                $slots = $page.find('div.catalog-page-slots'),
                xhr = null;

            if (!(1 === $template.length && 1 === $slots.length)) {
                throw 'Unexpected form composition';
            }

            function loadSlotsForm() {
                if (xhr) {
                    xhr.abort();
                    xhr = null;
                }

                var template = $template.val();

                $slots.loadingSpinner();

                xhr = $.ajax({
                    url: Router.generate('ekyna_product_catalog_admin_page_slots_form', {
                        'template': template,
                        'name': $page.attr('name')
                    }),
                    dataType: 'xml'
                });

                xhr.done(function(xml) {
                    $slots.loadingSpinner('off').empty();

                    var $form = $(xml).find('form');
                    if (1 !== $form.length) {
                        return;
                    }

                    $slots.append($($form.text()).children());

                    var form = Form.create($slots);
                    form.init();
                });
            }

            $template.on('change', loadSlotsForm);

            if ($slots.is(':empty')) {
                loadSlotsForm();
            }
        });

        return this;
    };

    return {
        init: function($element) {
            $element.catalogPageWidget();
        }
    };
});

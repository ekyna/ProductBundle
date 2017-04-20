define(['jquery', 'routing', 'ekyna-form', 'ekyna-spinner'], function($, Router, Form) {
    "use strict";

    /**
     * Catalog page widget
     */
    $.fn.catalogPageWidget = function() {

        this.each(function() {

            var $page = $(this),
                $template = $page.find('select.catalog-page-template'),
                $container = $page.find('div.catalog-page-form'),
                form = null, xhr = null;

            if (!(1 === $template.length && 1 === $container.length)) {
                throw 'Unexpected form composition';
            }

            function loadSlotsForm() {
                if (xhr) {
                    xhr.abort();
                    xhr = null;
                }

                if (form) {
                    form.destroy();
                }

                var template = $template.val();

                $container.loadingSpinner();

                xhr = $.ajax({
                    url: Router.generate('admin_ekyna_product_catalog_page_form', {
                        'template': template,
                        'name': $page.attr('name')
                    }),
                    dataType: 'xml'
                });

                xhr.done(function(xml) {
                    $container.loadingSpinner('off').empty();

                    var $form = $(xml).find('form');
                    if (1 !== $form.length) {
                        return;
                    }

                    $container.empty().append($($form.text()).children());

                    form = Form.create($container);
                    form.init();
                });
            }

            $template.on('change', loadSlotsForm);

            if ($container.is(':empty')) {
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

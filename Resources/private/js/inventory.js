define(['jquery', 'routing', 'ekyna-product/templates', 'ekyna-modal'], function($, Router, Templates, Modal) {

    var $list = $('#inventory_list'),
        $wait = $('#inventory_wait'),
        $none = $('#inventory_none'),
        $sort = $('#inventory_sort'),
        $form = $('form[name="inventory"]'),
        busy = false,
        productsXhr;

    /**
     * Returns the context form data.
     *
     * @returns object
     */
    function getContext() {
        var array = $form.serializeArray();

        var context = {};
        for (var i = 0; i < array.length; i++){
            context[array[i]['name']] = array[i]['value'];
        }

        return context;
    }

    /**
     * Updates the inventory list.
     */
    function updateList() {
        if (productsXhr) {
            productsXhr.abort();
        }

        $list.empty();
        $wait.show();
        $none.hide();

        productsXhr = $.ajax({
            url: Router.generate('ekyna_product_inventory_admin_products'),
            method: 'GET',
            dataType: 'json',
            data: getContext()
        })
        .done(function (data) {
            $wait.hide();

            if (data.products === undefined || 0 === data.products.length) {
                $none.show();
            } else {
                $.each(data.products, function (index, product) {
                    $(Templates['inventory_line.html.twig'].render(product)).appendTo($list);
                });
            }
        });
    }

    /**
     * Context form submit.
     */
    $form.on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();

        updateList();

        return false;
    });

    /**
     * Context form reset.
     */
    $form.on('reset', function() {
        updateList();
    });


    /**
     * Line's stock unit buttons click handler
     */
    $list.on('click', 'a.stock-units', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (busy) {
            return false;
        }

        busy = true;

        var productId = $(e.currentTarget).parents('tr').eq(0).data('id');
        if (!productId) {
            console.log('Undefined product id.');
            return false;
        }

        try {
            var modal = new Modal();
            modal.load({
                url: Router.generate('ekyna_product_inventory_admin_stock_units', {productId: productId}),
                method: 'GET'
            });
            $(modal).on('ekyna.modal.response', function () {
                busy = false;
            });
        } catch(e) {
            console.log(e);
            busy = false;
        }

        return false;
    });


    /**
     * Line's resupply buttons click handler
     */
    $list.on('click', 'a.resupply', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (busy) {
            return false;
        }

        busy = true;

        var productId = $(e.currentTarget).parents('tr').eq(0).data('id');
        if (!productId) {
            console.log('Undefined product id.');
            return false;
        }

        try {
            var modal = new Modal();
            modal.load({
                url: Router.generate('ekyna_product_inventory_admin_resupply', {productId: productId}),
                method: 'GET'
            });
            $(modal).on('ekyna.modal.response', function (e) {
                busy = false;

                if (e.contentType === 'json') {
                    e.preventDefault();

                    if (e.content.success) {
                        updateList();
                    }
                }
            });
        } catch(e) {
            console.log(e);
            busy = false;
        }

        return false;
    });

    /**
     * List sort headers click handler
     */
    $sort.on('click', 'th.sort a', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $a = $(e.currentTarget),
            sortDir = 'none';

        if ($a.hasClass('none')) {
            sortDir = 'asc';
        } else if ($a.hasClass('asc')) {
            sortDir = 'desc';
        }

        $sort.find('th.sort a').removeClass('asc desc').addClass('none');

        if (sortDir !== 'none') {
            $a.removeClass('none').addClass(sortDir);
            $form.find('input[name="inventory_search[sortBy]"]').val($a.data('by'));
            $form.find('input[name="inventory_search[sortDir]"]').val(sortDir);
        } else {
            $form.find('input[name="inventory_search[sortBy]"]').val(null);
            $form.find('input[name="inventory_search[sortDir]"]').val(null);
        }

        $form.trigger('submit');

        return false;
    });

    updateList();
});


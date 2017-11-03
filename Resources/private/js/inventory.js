define(['jquery', 'routing', 'ekyna-product/templates', 'ekyna-modal'], function($, Router, Templates, Modal) {

    var $window = $(window),
        $list = $('#inventory_list'),
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

    var eol = false, page = -1;

    /**
     * Refreshes the inventory list.
     */
    function refreshList() {
        if (productsXhr) {
            productsXhr.abort();
        }

        $list.empty();
        $wait.show();
        $none.hide();

        eol = false; page = -1;

        nextList();
    }

    /**
     * Display the inventory list's next page.
     */
    function nextList() {
        if (productsXhr) {
            productsXhr.abort();
        }

        if (eol) {
            return;
        }

        busy = true;

        page++;

        productsXhr = $.ajax({
            url: Router.generate('ekyna_product_inventory_admin_products', {page: page}),
            method: 'GET',
            dataType: 'json',
            data: getContext()
        })
        .done(function (data) {
            if (data.products === undefined || 0 === data.products.length) {
                if (page === 0) {
                    $none.show();
                }
                $wait.hide();
                eol = true;
            } else {
                // TODO Ugly. Need data.count value.
                if (30 > data.products.length) {
                    $wait.hide();
                    eol = true;
                }
                $.each(data.products, function (index, product) {
                    $(Templates['inventory_line.html.twig'].render(product)).appendTo($list);
                });
            }
        })
        .always(function() {
            busy = false;
        });
    }

    $window.on('scroll', function() {
        if (busy || eol) {
            return;
        }

        console.log($window.scrollTop(), $wait.offset().top);

        if (($window.scrollTop() + $window.height()) > $wait.offset().top) {
            nextList();
        }
    });

    /**
     * Context form submit.
     */
    $form.on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();

        refreshList();

        return false;
    });

    /**
     * Context form reset.
     */
    $form.on('reset', function() {
        refreshList();
    });


    function request(event, route) {
        event.preventDefault();
        event.stopPropagation();

        if (busy) {
            return false;
        }

        busy = true;

        var productId = $(event.currentTarget).parents('tr').eq(0).data('id');
        if (!productId) {
            console.log('Undefined product id.');
            return false;
        }

        try {
            var modal = new Modal();
            modal.load({
                url: Router.generate(route, {productId: productId}),
                method: 'GET'
            });
            $(modal).on('ekyna.modal.response', function (modalEvent) {
                busy = false;

                if (modalEvent.contentType === 'json') {
                    modalEvent.preventDefault();

                    if (modalEvent.content.success) {
                        refreshList();
                    }

                    modalEvent.modal.close();
                }
            });
        } catch(exception) {
            console.log(exception);
            busy = false;
        }

        return false;
    }


    /**
     * Line's quick edit buttons click handler
     */
    $list.on('click', 'a.quick-edit', function(e) {
        return request(e, 'ekyna_product_inventory_admin_quick_edit');
    });

    /**
     * Line's stock unit buttons click handler
     */
    $list.on('click', 'a.stock-units', function(e) {
        return request(e, 'ekyna_product_inventory_admin_stock_units');
    });

    /**
     * Line's stock unit buttons click handler
     */
    $list.on('click', 'a.treatment', function(e) {
        return request(e, 'ekyna_product_inventory_admin_customer_orders');
    });

    /**
     * Line's resupply buttons click handler
     */
    $list.on('click', 'a.resupply', function(e) {
        return request(e, 'ekyna_product_inventory_admin_resupply');
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

    refreshList();
});


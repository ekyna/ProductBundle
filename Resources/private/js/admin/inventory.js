define(
    ['require', 'jquery', 'routing', 'ekyna-dispatcher', 'ekyna-product/templates', 'ekyna-modal', 'ekyna-admin/barcode-scanner', 'ekyna-clipboard-copy'],
    function (require, $, Router, Dispatcher, Templates, Modal, Scanner) {

    var $window = $(window),
        $head = $('#inventory thead'),
        $body = $('#inventory tbody'),
        $foot = $('#inventory tfoot'),
        $wait = $('#inventory_wait').detach(),
        $none = $('#inventory_none').detach(),
        $contextForm = $('form[name="inventory"]'),
        busy = false,
        productsXhr;

    /**
     * Returns the context form data.
     *
     * @returns object
     */
    function getContext() {
        var array = $contextForm.serializeArray();

        var context = {};
        for (var i = 0; i < array.length; i++) {
            if (array[i]['value']) {
                context[array[i]['name']] = array[i]['value'];
            }
        }

        return context;
    }

    /**
     * Clears the context form data.
     */
    function clearContext() {
        $contextForm.find('table input').val(null).attr('value', null);
        $contextForm.find('table select').val(null).find('option').prop('selected', false);
    }

    var eol = false,
        page = -1;

    /**
     * Refreshes the inventory list.
     */
    function refreshList() {
        if (productsXhr) {
            productsXhr.abort();
        }

        $body.empty();

        eol = false;
        page = -1;

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

        $none.detach();
        $wait.appendTo($body);

        page++;

        productsXhr = $.ajax({
            url: Router.generate('admin_ekyna_product_inventory_products', {page: page}),
            method: 'GET',
            dataType: 'json',
            data: getContext()
        });

        productsXhr.done(handleResponse);

        productsXhr.always(function () {
            busy = false;
        });
    }

    function updateListHeight() {
        var height = $window.height() - $contextForm.outerHeight() - $head.outerHeight() - $foot.outerHeight();
        if (0 > height) {
            height = 0;
        }
        $body.height(height);
    }

    function request(event, route, parameters, method, handler) {
        method = method || 'GET';

        event.preventDefault();
        event.stopPropagation();

        if (busy) {
            return false;
        }

        busy = true;

        parameters = parameters || {};

        if (true === parameters) {
            var productId = $(event.currentTarget).parents('tr').eq(0).data('id');
            if (!productId) {
                console.log('Undefined product id.');
                return false;
            }
            parameters = {productId: productId};
        }

        var xhr = $.ajax({
            url: Router.generate(route, parameters),
            method: method
        });

        xhr.done(handler || handleResponse);

        xhr.always(function() {
            busy = false
        });
    }

    function requestModal(event, route, parameters, handler) {
        event.preventDefault();
        event.stopPropagation();

        if (busy) {
            return false;
        }

        busy = true;

        parameters = parameters || {};
        handler = handler || handleResponse;

        if (true === parameters) {
            var productId = $(event.currentTarget).parents('tr').eq(0).data('id');
            if (!productId) {
                console.log('Undefined product id.');
                return false;
            }
            parameters = {productId: productId};
        }

        try {
            var modal = new Modal();
            modal.load({
                url: Router.generate(route, parameters),
                method: 'GET'
            });
            $(modal).on('ekyna.modal.response', function (modalEvent) {
                busy = false;

                if (modalEvent.contentType === 'json') {
                    modalEvent.preventDefault();

                    handler(modalEvent.content);
                    /*if (modalEvent.content.success) {
                        refreshList();
                    }*/

                    modalEvent.modal.close();
                }
            });
            $(modal).on('ekyna.modal.load_fail', function() {
                busy = false;
            });
        } catch (exception) {
            console.log(exception);
            busy = false;
        }

        return false;
    }

    function handleResponse(data) {
        if (!data.hasOwnProperty('products') || 0 === data.products.length) {
            if (page === 0) {
                $none.appendTo($body);
            }
            eol = true;
            return;
        }

        if (!data.update) {
            $wait.detach();
        }

        $.each(data.products, function (index, product) {
            var $new = $(Templates['@EkynaProduct/Js/inventory_line.html.twig'].render(product)),
                $old = $body.find('tr[data-id=' + product.id + ']');

            if (1 === $old.length) {
                $old.replaceWith($new);
            } else {
                $new.appendTo($body);
            }
        });

        if (data.update) {
            return;
        }

        // TODO Ugly. Need data.count value.
        if (30 > data.products.length) {
            eol = true;
            return;
        }

        $wait.appendTo($body);
    }

    Scanner.init({
        //debug: true
    });
    Scanner.addListener(function(barcode) {
        if (productsXhr) {
            productsXhr.abort();
        }

        $body.empty();
        $none.detach();
        $wait.appendTo($body);

        eol = false;
        busy = true;
        page = 0;

        productsXhr = $.ajax({
            url: Router.generate('admin_ekyna_product_inventory_products', {page: page}),
            method: 'GET',
            dataType: 'json',
            data: {
                referenceCode: barcode
            }
        });

        productsXhr.done(handleResponse);

        productsXhr.always(function () {
            busy = false;
        });
    });

    /**
     * Line's bookmark buttons click handler
     */
    $body.on('click', 'a.bookmark', function (e) {
        var $icon = $(e.currentTarget).closest('a'),
            value = $icon.hasClass('fa-bookmark-o'),
            route = value
                ? 'admin_ekyna_product_product_bookmark_add'
                : 'admin_ekyna_product_product_bookmark_remove';

        return request(e, route, true, value ? 'POST' : 'DELETE', function() {
            if (value) {
                $icon.removeClass('fa-bookmark-o').addClass('fa-bookmark');
            } else {
                $icon.removeClass('fa-bookmark').addClass('fa-bookmark-o');
            }
        });
    });

    /**
     * Line's quick edit buttons click handler
     */
    $body.on('click', 'a.quick-edit', function (e) {
        return requestModal(e, 'admin_ekyna_product_inventory_quick_edit', true);
    });

    /**
     * Line's print label buttons click handler
     */
    $body.on('click', 'a.print-label', function (e) {
        var productId = $(e.currentTarget).parents('tr').eq(0).data('id');
        if (!productId) {
            console.log('Undefined product id.');
            return false;
        }

        var url = Router.generate('admin_ekyna_product_product_label', {
            'format': 'large',
            'id': [productId]
        });

        var win = window.open(url, '_blank');
        win.focus();
    });

    /**
     * Context form submit.
     */
    $contextForm.on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

        refreshList();

        return false;
    });

    /**
     * Context form reset.
     */
    $contextForm.on('reset', function () {
        clearContext();
        refreshList();
    });

    /**
     * Line's quick edit buttons click handler
     */
    $body.on('click', 'a.quick-edit', function (e) {
        return requestModal(e, 'admin_ekyna_product_inventory_quick_edit', true);
    });

    /**
     * Line's stock unit buttons click handler
     */
    $body.on('click', 'a.stock-units', function (e) {
        return requestModal(e, 'admin_ekyna_product_inventory_stock_units', true);
    });

    /**
     * Line's stock unit buttons click handler
     */
    $body.on('click', 'a.treatment', function (e) {
        return requestModal(e, 'admin_ekyna_product_inventory_customer_orders', true);
    });

    /**
     * Line's resupply buttons click handler
     */
    $body.on('click', 'a.resupply', function (e) {
        return requestModal(e, 'admin_ekyna_product_inventory_resupply', true);
    });

    $('button[name="batch_submit"]').on('click', function(e) {
        var ids = [];
        $('#inventory').serializeArray().forEach(function(obj) {
            ids.push(obj.value);
        });

        if (1 >= ids.length) {
            alert('Please select some products');

            return false;
        }

        return requestModal(e, 'admin_ekyna_product_inventory_batch_edit', {id: ids});
    });

    /**
     * List sort headers click handler
     */
    $head.on('click', 'th.sort a', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $a = $(e.currentTarget),
            sortDir = 'none';

        if ($a.hasClass('none')) {
            sortDir = 'asc';
        } else if ($a.hasClass('asc')) {
            sortDir = 'desc';
        }

        $head.find('th.sort a').removeClass('asc desc').addClass('none');

        if (sortDir !== 'none') {
            $a.removeClass('none').addClass(sortDir);
            $contextForm.find('input[name="inventory[sortBy]"]').val($a.data('by'));
            $contextForm.find('input[name="inventory[sortDir]"]').val(sortDir);
        } else {
            $contextForm.find('input[name="inventory[sortBy]"]').val(null);
            $contextForm.find('input[name="inventory[sortDir]"]').val(null);
        }

        $contextForm.trigger('submit');

        return false;
    });

    Dispatcher.on('ekyna_commerce.stock_units.change', function() {
        refreshList();
    });
    refreshList();

    $window.on('resize', updateListHeight);
    updateListHeight();

    $body.on('scroll', function () {
        if (busy || eol) {
            return;
        }

        if ($wait.offset().top < $body.height() + $contextForm.outerHeight() + $head.outerHeight()) {
            nextList();
        }
    });

    /* -----------------------------------------------------------------------------------------------------------------
     * Resource summary
     */
    require(['ekyna-admin/summary'], function(Summary) {
        Summary.init();
    });

    /* -----------------------------------------------------------------------------------------------------------------
     * Resource side detail
     */
    require(['ekyna-admin/side-detail'], function(SideDetail) {
        SideDetail.init();
    });
});


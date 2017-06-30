define(['jquery', 'routing', 'ekyna-product/templates', 'bootstrap/dialog'], function($, Router, Templates, BootstrapDialog) {

    var $list = $('#inventory_list'),
        $wait = $('#inventory_wait'),
        $none = $('#inventory_none'),
        $sort = $('#inventory_sort'),
        $form = $('form[name="inventory_search"]'),
        dialog = new BootstrapDialog({
            buttons: [
                {
                    label: 'Close',
                    action: function(dialog){
                        dialog.close();
                    }
                }
            ]
        });

    var productsXhr, stockUnitsXhr;

    function getParameters() {
        var array = $form.serializeArray();

        var data = {};
        for (var i = 0; i < array.length; i++){
            data[array[i]['name']] = array[i]['value'];
        }

        return data;
    }

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
            data: getParameters()
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

    $form.on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();

        updateList();

        return false;
    });

    $form.on('reset', function() {
        updateList();
    });

    $list.on('click', 'a.stock-units', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (stockUnitsXhr) {
            stockUnitsXhr.abort();
        }

        var productId = $(e.currentTarget).parents('tr').eq(0).data('id');
        if (!productId) {
            console.log('Undefined product id.');
            return false;
        }

        stockUnitsXhr = $.ajax({
            url: Router.generate('ekyna_product_inventory_admin_stock_units', {productId: productId}),
            method: 'GET',
            dataType: 'html'
        })
        .done(function(html) {
            dialog.setMessage($(html));
            dialog.setSize(BootstrapDialog.SIZE_WIDE);
            dialog.open();
        });

        return false;
    });

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

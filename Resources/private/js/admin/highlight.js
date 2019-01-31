define(['jquery', 'routing'], function ($, Router) {

    var $list = $('#highlight'),
        $products = $list.find('tbody > tr'),
        busy = false;

    function setBusy(value) {
        if (value) {
            if (busy) {
                return;
            }

            $list.find('th.sort a').addClass('disabled');
            $list.find('input, select').prop('disabled', true);
        } else {
            if (!busy) {
                return;
            }

            $list.find('th.sort a').removeClass('disabled');
            $list.find('input, select').prop('disabled', false);
        }

        busy = value;
    }

    function sort(by, dir) {
        if (busy) {
            return;
        }

        setBusy(true);

        var an, bn, callback;
        if (by === 'id') {
            callback = function (a, b) {
                an = parseInt(a.getAttribute('data-' + by));
                bn = parseInt(b.getAttribute('data-' + by));

                if (dir === "asc") {
                    return an - bn;
                } else if (dir === "desc") {
                    return bn - an;
                }

                return 0;
            };
        } else {
            callback = function (a, b) {
                an = a.getAttribute('data-' + by).toLowerCase();
                bn = b.getAttribute('data-' + by).toLowerCase();

                if (dir === "asc") {
                    if (an > bn)
                        return 1;
                    if (an < bn)
                        return -1;
                } else if (dir === "desc") {
                    if (an < bn)
                        return 1;
                    if (an > bn)
                        return -1;
                }

                return 0;
            };
        }

        $products.sort(callback).detach().appendTo($list.find('tbody'));

        setBusy(false);
    }

    function filter(by, value) {
        if (busy) {
            return;
        }

        setBusy(true);

        var test;
        if (by === 'brand' || by === 'designation' || by === 'reference') {
            var regex = new RegExp(value, 'i');
            test = function(v) {
                return regex.test(v);
            };
        } else {
            test = function (v) { return v === value; }
        }

        $products.each(function(index, tr) {
            if (test(tr.getAttribute('data-' + by))) {
                $(tr).show();
            } else {
                $(tr).hide();
            }
        });

        setBusy(false);
    }

    /**
     * Line's quick edit buttons click handler
     */
    $list.on('click', 'a.quick-edit', function (e) {
        return request(e, 'ekyna_product_inventory_admin_quick_edit');
    });

    /**
     * List sort headers handler
     */
    $list.on('click', 'th.sort a', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $a = $(e.currentTarget),
            sortDir = 'none';

        if ($a.hasClass('disabled')) {
            return false;
        }

        if ($a.hasClass('none')) {
            sortDir = 'asc';
        } else if ($a.hasClass('asc')) {
            sortDir = 'desc';
        }

        $list.find('th.sort a').removeClass('asc desc').addClass('none');
        if (sortDir === 'none') {
            $list.find('th.sort a[data-property="id"]').addClass('asc');
            sort('id', 'asc');
            return false;
        }

        $a.removeClass('none').addClass(sortDir);

        sort($a.data('property'), sortDir);

        return false;
    });

    /**
     * List filters headers handler
     */
    $list.on('change', '.filters input, .filters select', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $control = $(e.currentTarget),
            value = $control.val();

        if ($control.prop('disabled')) {
            return false;
        }

        if (value === '') {
            $products.show();
            return false;
        }

        filter($control.prop('name'), $control.val());

        return false;
    });

    /**
     * Controls handler
     */
    $list.on('change', '.products input, .products select', function (e) {
        if (busy) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        var $control = $(e.currentTarget),
            $tr = $control.closest('tr'),
            property = $control.prop('name'),
            value = $control.val();

        $tr.addClass('warning');

        if ($control.prop('disabled')) {
            return false;
        }

        setBusy(true);

        if (property === 'visible') {
            value = value ? 1 : 0;
        }

        $.ajax({
            url: Router.generate('ekyna_product_highlight_admin_update', {
                productId: $tr.data('id')
            }),
            method: 'POST',
            dataType: 'json',
            data: {property: property, value: value}
        })
        .done(function(data) {
            $tr.removeClass('warning danger').addClass('success');
            $tr.data(property, data[property]);
            setTimeout(function() {
                $tr.removeClass('success');
            }, 1500);
        })
        .fail(function() {
            if (property === 'visible') {
                $control.prop('checked', !!$tr.data('visible'));
            } else {
                $control.val($tr.data(property));
            }
            $tr.removeClass('warning success').addClass('danger');
        })
        .always(function() {
            setBusy(false);
        });

        return false;
    });
});

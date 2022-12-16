define(
    ['require', 'jquery', 'routing', 'ekyna-product/templates', 'ekyna-modal', 'ekyna-admin/barcode-scanner', 'ekyna-clipboard-copy'],
    function (require, $, Router, Templates, Modal, Scanner) {

        const
            $list = $('#inventory_list'),
            $wait = $('#inventory_wait'),
            $none = $('#inventory_none'),
            route_prefix = 'admin_ekyna_product_inventory_app_',
            $displayValid = $('#display_valid'),
            $displayEndOfLife = $('#display_end_of_life');

        let busy = false,
            display_valid = $displayValid.prop('checked'),
            display_end_of_life = $displayEndOfLife.prop('checked');


        function toggleRows() {
            if (display_valid) {
                $list.addClass('display-valid');
            } else {
                $list.removeClass('display-valid');
            }
            if (display_end_of_life) {
                $list.addClass('display-end-of-life');
            } else {
                $list.removeClass('display-end-of-life');
            }
        }

        function list() {
            if (busy) {
                return;
            }

            busy = true;
            $wait.show();
            $none.hide();
            $list.empty();

            fetch(Router.generate(route_prefix + 'list'), {
                headers: {
                    'Content-Type': 'application/json'
                },
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch product list');
                    }

                    return response.json()
                })
                .then((data) => {
                    console.log(data)

                    if (!(data.hasOwnProperty('products') && data.products instanceof Array)) {
                        return;
                    }

                    if (0 === data.products.length) {
                        $none.show();

                        return;
                    }

                    for (const product of data.products) {
                        $(Templates['@EkynaProduct/Js/inventory_line.html.twig'].render(product)).appendTo($list);
                    }

                    toggleRows();
                })
                .finally(() => {
                    busy = false;
                    $wait.hide();
                });
        }

        list();

        function update(product) {
            const $new = $(Templates['@EkynaProduct/Js/inventory_line.html.twig'].render(product)),
                $old = $list.find('div[data-id=' + product.id + ']');

            if (1 === $old.length) {
                $old.replaceWith($new);
            } else {
                $new.appendTo($list);
            }
        }

        function modal(event, route) {
            const id = $(event.currentTarget).parents('div').eq(1).data('id');
            if (!id) {
                throw new Error('Undefined id');
            }

            if (busy) {
                return false;
            }

            busy = true;

            try {
                const modal = new Modal();
                modal.load({
                    url: Router.generate(route_prefix + route, {id: id}),
                    method: 'GET'
                });
                $(modal).on('ekyna.modal.response', function (modalEvent) {
                    busy = false;

                    if (modalEvent.contentType === 'json') {
                        modalEvent.preventDefault();

                        update(modalEvent.content.product);

                        modalEvent.modal.close();
                    }
                });
                $(modal).on('ekyna.modal.load_fail', function () {
                    busy = false;
                });
            } catch (exception) {
                console.log(exception);
                busy = false;
            }

            return false;
        }

        function request(event, route, confirmMessage) {
            if (!confirm(confirmMessage)) {
                return;
            }

            const id = $(event.currentTarget).parents('div').eq(1).data('id');
            if (!id) {
                throw new Error('Undefined id');
            }

            if (busy) {
                return false;
            }

            busy = true;

            fetch(Router.generate(route_prefix + route, {id: id}), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    return response.json()
                })
                .then((data) => {
                    console.log(data)

                    if (!(data.hasOwnProperty('product') && typeof data.product === 'object')) {
                        return;
                    }

                    update(data.product);
                })
                .finally(() => {
                    busy = false;
                });
        }

        $displayValid.on('change', () => {
            display_valid = $displayValid.prop('checked');

            toggleRows();
        });

        $displayEndOfLife.on('change', () => {
            display_end_of_life = $displayEndOfLife.prop('checked');

            toggleRows();
        });

        $list.on('click', 'a.count', (e) => modal(e, 'count'));

        $list.on('click', 'a.validate', (e) => request(e, 'validate', 'Valider le stock ?'));

        $list.on('click', 'a.end-of-life', (e) => request(e, 'end_of_life', 'Confirmer «En fin de vie» ?'));

        Scanner.init({
            //debug: true
        });
        Scanner.addListener((gtin) => {
            $list.find('> div').removeClass('focus');
            let $row = $list.find('div[data-gtin="' + gtin + '"]');
            if (0 === $row.length) {
                return;
            }
            $row.addClass('focus');
            let top = $row.offset().top;
            $('html, body').scrollTop(top - 50);
        });
    });

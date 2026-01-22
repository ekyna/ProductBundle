define(
    ['routing', 'ekyna-product/templates', 'ekyna-modal'],
    function (Router, Templates, EkynaModal) {
        function SaleBrowseProducts(element) {
            console.log('SaleAddProduct constructor')
            this.element = element;
            this.list = this.element.querySelector('main');
            this.busy = false;

            this.init('SaleAddProduct constructor');
        }

        SaleBrowseProducts.prototype.init = function() {
            const categories = this.element.querySelectorAll('nav a');
            for (const category of categories) {
                category.addEventListener('click', this.categoryClickHandler.bind(this));
            }
        };

        SaleBrowseProducts.prototype.categoryClickHandler = function (event) {
            const id = event.target.attributes['data-id'].value;
            this.loadProducts(id);
        };

        SaleBrowseProducts.prototype.thumbAddButtonClickHandler = function (event) {
            this.busy = true;

            const productId = parseInt(event.target.attributes['data-id'].value);

            EkynaModal.getInstance().load({
                url: this.element.attributes['data-add-item-path'].value,
                method: 'POST',
                data: {
                    provider: 'product',
                    identifier: productId,
                }
            });
        };

        SaleBrowseProducts.prototype.displayProducts = function (data) {
            data.default_image = this.element.attributes['data-default-image'].value;
            this.list.innerHTML = Templates['@EkynaProduct/Js/sale_product_list.html.twig'].render(data);

            this.list.querySelectorAll('a').forEach((a) => {
                a.addEventListener('click', this.thumbAddButtonClickHandler.bind(this));
            });
        };

        SaleBrowseProducts.prototype.loadProducts = function (categoryId) {
            console.log('loadProducts', categoryId);

            if (this.busy) {
                return;
            }

            this.busy = true;

            this.list.replaceChildren();

            // TODO Loading

            fetch(Router.generate('admin_ekyna_product_sale_product_list', {categoryId}), {
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
                    this.displayProducts(data);
                })
                .finally(() => {
                    this.busy = false;
                });
        };

        return SaleBrowseProducts;
    }
);

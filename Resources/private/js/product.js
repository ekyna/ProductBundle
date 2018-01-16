define(['jquery', 'ekyna-dispatcher', 'ekyna-commerce/stock-units', 'ekyna-ui'], function($, Dispatcher, StockUnits) {

    var $stockRefreshBtn = $('#stock-view-refresh'),
        $stockView = $('#stock-view'),
        stockXhr = null;

    function refreshStock(skipUpdate) {
        $stockView.loadingSpinner();

        if (null !== stockXhr) {
            stockXhr.abort();
            stockXhr = null;
        }

        var url = $stockRefreshBtn.attr('href');

        if (!!skipUpdate) {
            url += '?no-update=1'
        }

        stockXhr = $.ajax({
            url: url,
            dataType: 'xml'
        });

        stockXhr.done(function(xml) {
            var $view = $(xml).find('stockView');
            if (1 === $view.size()) {
                $stockView.html($view.text());
            }
        });
    }

    $stockRefreshBtn.on('click', function(e) {
        e.preventDefault();

        refreshStock();

        return false;
    });

    Dispatcher.on('ekyna_commerce.stock_units.change', function() {
        refreshStock(true);
    });

    new StockUnits('stock-units-view');
});

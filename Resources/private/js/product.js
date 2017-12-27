define(['jquery', 'ekyna-ui'], function($) {

    console.log('test');

    var $stockRefreshBtn = $('#stock-view-refresh'),
        $stockView = $('#stock-view'),
        stockXhr = null;

    console.log($stockRefreshBtn.size(), $stockView.size());

    $stockRefreshBtn.on('click', function(e) {
        e.preventDefault();

        $stockView.loadingSpinner();

        if (null !== stockXhr) {
            stockXhr.abort();
            stockXhr = null;
        }

        stockXhr = $.ajax({
            url: $stockRefreshBtn.attr('href'),
            dataType: 'xml'
        });

        stockXhr.done(function(xml) {
            var $view = $(xml).find('stockView');
            if (1 === $view.size()) {
                $stockView.html($view.text());
            }
        });

        return false;
    });

});

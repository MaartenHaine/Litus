(function ($) {
    var defaults = {
        tCurrentCustomer: 'Current Customer',
        tComments: 'Comments',
        tQueue: 'Queue',
        tConclude: 'Finish',
        tCancel: 'Cancel',

        saveComment: function (id, comment) {},
        showQueue: function () {},
        finish: function (id, articles) {},
        cancel: function (id) {},
        translateStatus: function (status) {return status},
        addArticle: function (id, barcode) {},
    };

    var methods = {
        init : function (options) {
            var settings = $.extend(defaults, options);
            $(this).saleInterface($.extend({
                isSell: false,
                conclude: settings.finish,
            }, settings));

            var $this = $(this);
            $(this).data('collectSettings', settings);

            _init($this);
            return this;
        },
        show : function (data) {
            currentView = 'collect';
            $(this).saleInterface('show', data);
            return this;
        },
        hide : function (data) {
            $(this).saleInterface('hide');
            return this;
        },
        gotBarcode : function (barcode) {
            $(this).saleInterface('gotBarcode', barcode);
            return this;
        },
        addArticle : function (data) {
            $(this).saleInterface('addArticle', data);
            return this;
        },
    };

    $.fn.collect = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' +  method + ' does not exist on $.collect');
        }
    };

    function _init($this) {
        var settings = $this.data('collectSettings');
    }
})(jQuery);
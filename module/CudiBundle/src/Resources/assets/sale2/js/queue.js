(function ($) {
    var defaults = {
        width: 800,
        widthNum: 50,
        widthName: 330,
        widthStatus: 120,
        widthActions: 230,
        height: 300,

        tQueueTitle: 'Queue',
        tUniversityIdentification: 'University Identification',
        tPrint: 'Print',
        tDone: 'Done',
        tCancel: 'Cancel',
        tSell: 'Sell',
        tHold: 'Hold',
        tUnhold: 'Unhold',
        tHideHold: 'Hide Hold',
        tUndoLastSelling: 'Undo Last Selling - F6',
        tPrintNext: 'Print Next - F7',
        tNotFoundInQueue: '<i><b>{{ name }}</b> was not found in the queue.</i>',
        tAddToQueue: 'Add to queue',

        translateStatus: function (status) {return status},
        sendToSocket: function (text) {},
    };

    var lastPrinted = 0;
    var lastSold = 0;

    var methods = {
        init : function (options) {
            var settings = $.extend(defaults, options);
            var $this = $(this);
            $(this).data('queueSettings', settings);

            _init($this);
            return this;
        },
        show : function (options) {
            currentView = 'queue';
            var permantent = options == undefined || options.permanent == undefined ? true : options.permanent;
            $(this).permanentModal({closable: !permantent});
            return this;
        },
        updateQueue : function (data) {
            _updateQueue($(this), data);
            return this;
        },
        setLastSold : function (data) {
            lastSold = data;
            $(this).find('.undoLastSelling').toggle(lastSold > 0);
            return this;
        }
    };

    $.fn.queue = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' +  method + ' does not exist on $.sale');
        }
    };

    $.queue = function (options) {
        return $('<div>').queue(options);
    }

    function _init($this) {
        var settings = $this.data('queueSettings');

        $this.addClass('modal fade').html('').append(
            $('<div>', {'class': 'modal-header'}).append(
                $('<a>', {'class': 'close'}).html('&times;').click(function () {$this.modal('hide')}),
                $('<div>', {'class': 'form-search'}).append(
                    $('<div>', {'class': 'input-append pull-right'}).append(
                        filterText = $('<input>', {'type': 'text', 'class': 'input-medium search-query filterText', 'placeholder': settings.tUniversityIdentification}),
                        clearFilter = $('<span>', {'class': 'add-on'}).css('cursor', 'pointer').append(
                            $('<span>', {'class': 'icon-remove'})
                        )
                    )
                ),
                $('<h3>').html(settings.tQueueTitle)
            ),
            $('<div>', {'class': 'modal-body'}).append(
                $('<table>', {'class': 'table table-striped table-bordered'}).append(
                    $('<thead>').append(
                        $('<tr>').append(
                            $('<th>', {'style': 'width: ' + settings.widthNum + 'px'}).html('Num'),
                            $('<th>', {'style': 'width: ' + settings.widthName + 'px'}).html('Name'),
                            $('<th>', {'style': 'width: ' + settings.widthStatus + 'px'}).html('Status'),
                            $('<th>', {'style': 'width: ' + settings.widthActions + 'px'}).html('Action')
                        )
                    ).css('display', 'block'),
                    $('<tbody>').css({
                        'display': 'block',
                        'max-height': settings.height + 'px',
                        'overflow': 'auto',
                    })
                )
            ),
            $('<div>', {'class': 'modal-footer'}).append(
                $('<label>', {'class': 'checkbox pull-left'}).append(
                    hideHold = $('<input>', {'class': 'hideHold', 'type': 'checkbox', 'checked': 'checked'}),
                    settings.tHideHold
                ),
                undoLastSelling = $('<button>', {'class': 'btn btn-danger hide undoLastSelling'}).append(
                    $('<i>', {'class': 'icon-arrow-left icon-white'}),
                    settings.tUndoLastSelling
                ),
                printNext = $('<button>', {'class': 'btn btn-success'}).append(
                    $('<i>', {'class': 'icon-print icon-white'}),
                    settings.tPrintNext
                )
            )
        ).css({
            'width': settings.width + 'px',
            'margin-left': - settings.width / 2 + 'px',
        });

        hideHold.change(function () {
            $this.find('tbody tr').each(function () {
                _toggleVisibility($this, $(this));
            });
        });

        clearFilter.click(function () {
            filterText.val('');
            filterText.trigger('keyup');
        });

        filterText.keyup(function () {
            var filter = $(this).val().toLowerCase();
            var pattern = new RegExp(/[a-z][0-9]{7}/);

            if (pattern.test(filter)) {
                var found = false;
                $this.find('tbody tr').each(function () {
                    if ($(this).data('info').university_identification.toLowerCase().indexOf(filter) == 0)
                        found = true;
                    return !found;
                });

                if (!found) {
                    $this.find('tbody').append(
                        $('<tr>', {'id': 'addToQueue'}).append(
                            $('<td>', {'style': 'width: ' + settings.widthNum + 'px'}),
                            $('<td>', {'style': 'width: ' + settings.widthName + 'px'}).html(
                                settings.tNotFoundInQueue.replace('{{ name }}', filter)
                            ),
                            $('<td>', {'style': 'width: ' + settings.widthStatus + 'px'}),
                            $('<td>', {'style': 'width: ' + settings.widthActions + 'px'}).css('padding', '3px 8px').append(
                                $('<button>', {'class': 'btn btn-success'}).html(settings.tAddToQueue).data('id', filter).click(function () {
                                    settings.sendToSocket(
                                        JSON.stringify({
                                            'command': 'action',
                                            'action': 'addToQueue',
                                            'universityIdentification': filter,
                                        })
                                    );
                                })
                            )
                        )
                    );
                } else {
                    $this.find('tbody #addToQueue').remove();
                }
            } else {
                $this.find('tbody #addToQueue').remove();
            }

            $this.find('tbody tr').each(function () {
                _toggleVisibility($this, $(this));
            });
        });

        printNext.click(function () {
            $this.find('tbody tr').each(function () {
                if ($(this).data('info').status == 'signed_in' && $(this).data('info').id > lastPrinted) {
                    lastPrinted = $(this).data('info').id;
                    $(this).find('.startCollecting').click();
                }
            });
        });

        undoLastSelling.click(function () {
            if (lastSold > 0) {
                settings.sendToSocket(
                    JSON.stringify({
                        'command': 'action',
                        'action': 'undoLastSelling',
                        'id': lastSold,
                    })
                );
            }
            $(this).hide();
        });
    }

    function _updateQueue($this, data) {
        var settings = $this.data('queueSettings');
        var tbody = $this.find('tbody');
        var inQueue = [];

        $(data).each(function () {
            inQueue.push(parseInt(this.id, 10));

            var item = tbody.find('#item-' + this.id);
            if (item.length == 0) {
                item = _createItem(settings, this);
                tbody.append(item);
            } else {
                _updateItem(settings, item, this)
            }

            _showActions($this, item);
            _toggleVisibility($this, item);
        });

        tbody.find('tr').each(function () {
            if ($.inArray(parseInt($(this).data('info').id, 10), inQueue) < 0)
                $(this).remove();
        });
    }

    function _showActions($this, row) {
        var data = row.data('info');

        switch (data.status) {
            case 'signed_in':
                if (currentView == 'sale' || currentView == 'collecting') {
                    row.find('.hold').show();
                    row.find('.startCollecting, .stopCollecting, .cancelCollecting, .startSelling, .cancelSelling, .unhold').hide();
                } else {
                    row.find('.startCollecting, .hold').show();
                    row.find('.stopCollecting, .cancelCollecting, .startSelling, .cancelSelling, .unhold').hide();
                }
                break;
            case 'collecting':
                row.find('.stopCollecting, .cancelCollecting, .hold').show();
                row.find('.startCollecting, .startSelling, .cancelSelling, .unhold').hide();
                break;
            case 'collected':
                if (currentView == 'sale' || currentView == 'collecting') {
                    row.find('.hold').show();
                    row.find('.startCollecting, .stopCollecting, .cancelCollecting, .startSelling, .cancelSelling, .unhold').hide();
                } else {
                    row.find('.startSelling, .hold').show();
                    row.find('.startCollecting, .stopCollecting, .cancelCollecting, .cancelSelling, .unhold').hide();
                }
                break;
            case 'selling':
                row.find('.cancelSelling, .hold').show();
                row.find('.startCollecting, .stopCollecting, .cancelCollecting, .startSelling, .unhold').hide();
                break;
            case 'hold':
                row.find('.unhold').show();
                row.find('.startCollecting, .stopCollecting, .cancelCollecting, .startSelling, .cancelSelling, .hold').hide();
                break;
        }

        if (data.locked)
            row.find('button').addClass('disabled');
        else
            row.find('button').removeClass('disabled');
    }

    function _updateItem(settings, row, data) {
        data.status = 'signed_in';
        row.find('.number').html(data.number);
        row.find('.name').html('').append(
            data.name,
            ' ',
            (data.payDesk ? $('<span>', {'class': 'label label-info'}).html(data.payDesk) : '')
        );
        row.find('.status').html(settings.translateStatus(data.status));
        row.data('info', data);
    }

    function _createItem(settings, data) {
        var row = $('<tr>', {'id': 'item-' + data.id}).append(
            $('<td>', {'class': 'number', 'style': 'width: ' + settings.widthNum + 'px'}),
            $('<td>', {'class': 'name', 'style': 'width: ' + settings.widthName + 'px'}),
            $('<td>', {'class': 'status', 'style': 'width: ' + settings.widthStatus + 'px'}),
            $('<td>', {'style': 'width: ' + settings.widthActions + 'px'}).css('padding', '3px 8px').append(
                startCollecting = $('<button>', {'class': 'btn btn-success startCollecting'}).html(settings.tPrint).hide(),
                stopCollecting = $('<button>', {'class': 'btn btn-success stopCollecting'}).html(settings.tDone).hide(),
                cancelCollecting = $('<button>', {'class': 'btn btn-danger cancelCollecting'}).html(settings.tCancel).hide(),
                startSelling = $('<button>', {'class': 'btn btn-success startSelling'}).html(settings.tSell).hide(),
                cancelSelling = $('<button>', {'class': 'btn btn-danger cancelSelling'}).html(settings.tCancel).hide(),
                hold = $('<button>', {'class': 'btn btn-warning hold'}).html(settings.tHold).hide(),
                unhold = $('<button>', {'class': 'btn btn-warning unhold'}).html(settings.tUnhold).hide()
            )
        );

        _updateItem(settings, row, data);

        row.find('button').css('margin-right', '5px');
        hold.css({
            'float': 'right',
            'margin-right': 0,
        });

        startCollecting.click(function () {
            if ($(this).is('.disabled'))
                return;
            settings.sendToSocket(
                JSON.stringify({
                    'command': 'action',
                    'action': 'startCollecting',
                    'id': $(this).closest('tr').data('info').id,
                })
            );
        });

        stopCollecting.click(function () {
            if ($(this).is('.disabled'))
                return;
            settings.sendToSocket(
                JSON.stringify({
                    'command': 'action',
                    'action': 'stopCollecting',
                    'id': $(this).closest('tr').data('info').id,
                })
            );
        });

        cancelCollecting.click(function () {
            if ($(this).is('.disabled'))
                return;
            settings.sendToSocket(
                JSON.stringify({
                    'command': 'action',
                    'action': 'cancelCollecting',
                    'id': $(this).closest('tr').data('info').id,
                })
            );
        });

        startSelling.click(function () {
            if ($(this).is('.disabled'))
                return;
            settings.sendToSocket(
                JSON.stringify({
                    'command': 'action',
                    'action': 'startSelling',
                    'id': $(this).closest('tr').data('info').id,
                })
            );
        });

        cancelSelling.click(function () {
            if ($(this).is('.disabled'))
                return;
            settings.sendToSocket(
                JSON.stringify({
                    'command': 'action',
                    'action': 'cancelSelling',
                    'id': $(this).closest('tr').data('info').id,
                })
            );
        });

        hold.click(function () {
            if ($(this).is('.disabled'))
                return;
            settings.sendToSocket(
                JSON.stringify({
                    'command': 'action',
                    'action': 'hold',
                    'id': $(this).closest('tr').data('info').id,
                })
            );
        });

        unhold.click(function () {
            if ($(this).is('.disabled'))
                return;
            settings.sendToSocket(
                JSON.stringify({
                    'command': 'action',
                    'action': 'unhold',
                    'id': $(this).closest('tr').data('info').id,
                })
            );
        });

        return row;
    }

    function _toggleVisibility($this, row) {
        var show = true;
        if ($this.find('.hideHold').is(':checked') && row.data('info').status == 'hold')
            show = false;

        var filter = $this.find('.filterText').val().toLowerCase();
        if (filter.length > 0) {
            show = false;
            if (row.data('info').name.toLowerCase().indexOf(filter) >= 0 || row.data('info').university_identification.toLowerCase().indexOf(filter) >= 0)
                show = true
        }

        if (show)
            $this.find('tbody #addToQueue').remove();

        row.toggle(show);
    }
})(jQuery);
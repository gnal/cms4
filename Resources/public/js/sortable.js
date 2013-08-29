(function($) {
    "use strict";
    var $table = $('table.table-sortable');
    var fixHelper = function(e, ui) {
        ui.children().each(function() {
            var $this = $(this);
            $this.width($this.width());
        });
        return ui;
    };

    $table.children('tbody').sortable({
        helper: fixHelper,
        handle: $('td'),
        cancel: 'a,button'
    }).disableSelection();

    $table.on('sortstop', function(e, ui) {
        var next = null,
            prev = null;

        if (typeof $(ui.item).next('tr').attr('id') !== 'undefined') {
            next = $(ui.item).next('tr').attr('id');
        }

        if (typeof $(ui.item).prev('tr').attr('id') !== 'undefined') {
            prev = $(ui.item).prev('tr').attr('id');
        }

        $.ajax($(this).data('url'), {
            data: {
                'current': $(ui.item).attr('id'),
                'next': next,
                'prev': prev
            },
        });
    });
})(jQuery);

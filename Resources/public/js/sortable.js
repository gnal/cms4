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
            next = $(ui.item).next('tr').attr('id').substr(2);
        }

        if (typeof $(ui.item).prev('tr').attr('id') !== 'undefined') {
            prev = $(ui.item).prev('tr').attr('id').substr(2);
        }

        $.ajax("{{ admin.container.get('msi_trip_trip_quote_item_admin').genUrl('sort') }}", {
            data: {
                'current': $(ui.item).attr('id').substr(2),
                'next': next,
                'prev': prev
            },
        });
    });
})(jQuery);

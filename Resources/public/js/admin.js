(function($) {
    "use strict";
    var $table = $('table.table');
    var loadingCellIds = [];

    $table.on('click', 'a.msi_admin_admin_change', function(e) {
        e.preventDefault();

        var $this = $(this),
            iconTrue = $this.data('icon-true'),
            iconFalse = $this.data('icon-false'),
            BadgeTrue = $this.data('btn-true'),
            BadgeFalse = $this.data('btn-false'),
            cellId = $this.closest('tr').attr('id');

        if ($.inArray(cellId, loadingCellIds) !== -1) {
            return;
        }
        loadingCellIds.push(cellId);

        $this.html('<span class="icon-spinner icon-spin icon-large"></span>');

        $.ajax($this.attr('href'), {
            success: function() {
                if ($this.hasClass(BadgeTrue)) {
                    var i = '<span class="icon-large"><span class="hide">0</span></span>';
                    $this
                        .removeClass(BadgeTrue)
                        .addClass(BadgeFalse)
                        .empty()
                        .html(i)
                        .children()
                        .removeClass(iconTrue)
                        .addClass(iconFalse);
                } else {
                    var i = '<span class="icon-large"><span class="hide">1</span></span>';
                    $this
                        .removeClass(BadgeFalse)
                        .addClass(BadgeTrue)
                        .empty()
                        .html(i)
                        .children()
                        .removeClass(iconFalse)
                        .addClass(iconTrue);
                }

                loadingCellIds.splice(loadingCellIds.indexOf(cellId), 1);
            }
        });
    });

    $('form.form-horizontal').on('click', 'a.msi_admin_admin_deleteUpload', function(e) {
        var $this = $(this);
        if (!window.confirm('Are you sure you want to delete this file?')) {
            return;
        }
        $.ajax($this.attr('href'), {
            success: function(data) {
                if (data.status === 'ok') {
                    $this.closest('.btn-toolbar').remove();
                }
            }
        });
        e.preventDefault();
    });

    $('body').on('click', '.msi_admin_delete', function(e) {
        e.preventDefault();
        var $this = $(this);
        if (!window.confirm('Are you sure you want to delete this entry?')) {
            return;
        }
        $this.closest('tr').remove();
        $.ajax($this.attr('href'), {
            type: 'POST',
            success: function(response) {
                // $this.closest('tr').remove();
                // $table.html($(response).find('#grid').html());
                // $('#gridCount').html($(response).find('#gridCount').html());
            }
        });
    });

    $('form#limitForm select').on('change', function() {
        $(this).closest('form').submit();
    });

    $('body').on('click', '.btn-select-all', function(e) {
        $(this).closest('.btn-toolbar').next('div').find('input').prop('checked', true);
        e.preventDefault();
    });

    $('body').on('click', '.btn-select-none', function(e) {
        $(this).closest('.btn-toolbar').next('div').find('input').prop('checked', false);
        e.preventDefault();
    });

    // char count for textareas

    $('textarea').on('keyup', function() {
        var $this = $(this);
        $this.siblings('div.char-count').text($this.val().length);
    });

    $(window).on('load', function() {
        $.each($('textarea'), function(i, v) {
            var $v = $(v);
            $v.siblings('div.char-count').text($v.val().length);
        });
    });

    // Helper function to add query string params to url

    jQuery.parameterize = function(params, url) {
        var url = url || window.location.href;

        url = url.replace('#', '');

        if (url.match(/\?/)) {
            var hasQuery = true;
        } else {
            var hasQuery = false;
            url = url+'?';
        }

        var i = 0;
        for (var x in params) {
            // if query already has this parameters then replace it instead of appending it
            if (url.indexOf('?'+x+'=') != -1 || url.indexOf('&'+x+'=') != -1) {
                var regex = new RegExp(x+'=[a-z]+');
                url = url.replace(regex, '?'+x+'='+params[x]);
                continue;
            } else {
                if (!hasQuery && i === 0) {
                    url += x+'='+params[x];
                } else {
                    url += '&'+x+'='+params[x];
                }
            }
            i++;
        }

        return url;
    };

    $('body').on('click', '.switch_data_locale', function(e) {
        e.preventDefault();
        window.location.href = $.parameterize({locale: $(this).data('locale')});
    });
})(jQuery);

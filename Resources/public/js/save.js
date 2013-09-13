(function($) {
    "use strict";
    var $body = $('body');
    var $form = $('form.robojax-form');
    $body.on('click', '.msi_admin_save', function(e) {
        $form.submit();
        e.preventDefault();
    });
    $body.on('click', '.msi_admin_save_add', function(e) {
        $form.attr('action', $.parameterize({'alt': 'add'}));
        $form.submit();
        e.preventDefault();
    });
    $body.on('click', '.msi_admin_save_quit', function(e) {
        $form.attr('action', $.parameterize({'alt': 'quit'}));
        $form.submit();
        e.preventDefault();
    });
})(jQuery);

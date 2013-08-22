(function($) {
    "use strict";
    var $body = $('body');
    var $form = $('form.form-crud');
    $body.on('click', '.msi_admin_save', function(e) {
        console.log('dada');
        $form.submit();
        e.preventDefault();
    });
    $body.on('click', '.msi_admin_save_add', function(e) {
        $form.attr('action', $.parameterize($form.attr('action'), {'alt': 'add'}));
        $form.submit();
        e.preventDefault();
    });
    $body.on('click', '.msi_admin_save_quit', function(e) {
        $form.attr('action', $.parameterize($form.attr('action'), {'alt': 'quit'}));
        $form.submit();
        e.preventDefault();
    });
})(jQuery);

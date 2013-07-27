(function($) {
    "use strict";
    var $content = $('#content');
    var $form = $('form.form-crud');
    $content.on('click', '.msi_cmf_save', function(e) {
        $form.submit();
        e.preventDefault();
    });
    $content.on('click', '.msi_cmf_save_add', function(e) {
        $form.attr('action', $.parameterize($form.attr('action'), {'alt': 'add'}));
        $form.submit();
        e.preventDefault();
    });
    $content.on('click', '.msi_cmf_save_quit', function(e) {
        $form.attr('action', $.parameterize($form.attr('action'), {'alt': 'quit'}));
        $form.submit();
        e.preventDefault();
    });
})(jQuery);
